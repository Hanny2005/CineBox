<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public const HOLD_MINUTES = 10;   // thời gian giữ ghế chờ thanh toán
    public const MAX_SEATS = 8;       // số ghế tối đa mỗi đơn
    public const CANCEL_BEFORE_HOURS = 2; // khách được hủy vé đã thanh toán trước giờ chiếu

    /** Chuyển các đơn pending quá hạn sang expired và nhả ghế. */
    public function expireStale(): int
    {
        $ids = Booking::where('status', BookingStatus::Pending)
            ->where('expires_at', '<', now())
            ->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        DB::transaction(function () use ($ids) {
            Booking::whereIn('id', $ids)->update([
                'status' => BookingStatus::Expired->value,
                'updated_at' => now(),
            ]);
            BookingSeat::whereIn('booking_id', $ids)->update(['active_showtime_id' => null]);
        });

        return $ids->count();
    }

    /** @return int[] id các ghế đang bị giữ hoặc đã bán trong suất chiếu */
    public function takenSeatIds(Showtime $showtime): array
    {
        return BookingSeat::where('active_showtime_id', $showtime->id)->pluck('seat_id')->all();
    }

    /**
     * Tạo đơn giữ ghế. Ràng buộc UNIQUE(active_showtime_id, seat_id) đảm bảo
     * hai người không thể giữ cùng một ghế, kể cả khi bấm cùng lúc.
     */
    public function create(User $user, Showtime $showtime, array $seatIds): Booking
    {
        $this->expireStale();

        if (! $showtime->is_active || $showtime->start_time->isPast()) {
            throw ValidationException::withMessages(['seats' => 'Suất chiếu đã bắt đầu hoặc không còn mở bán.']);
        }

        $seatIds = array_values(array_unique(array_map('intval', $seatIds)));
        if (count($seatIds) < 1 || count($seatIds) > self::MAX_SEATS) {
            throw ValidationException::withMessages(['seats' => 'Vui lòng chọn từ 1 đến '.self::MAX_SEATS.' ghế.']);
        }

        try {
            return DB::transaction(function () use ($user, $showtime, $seatIds) {
                $seats = Seat::where('room_id', $showtime->room_id)
                    ->where('is_active', true)
                    ->whereIn('id', $seatIds)
                    ->get();

                if ($seats->count() !== count($seatIds)) {
                    throw ValidationException::withMessages(['seats' => 'Có ghế không hợp lệ, vui lòng chọn lại.']);
                }

                $booking = Booking::create([
                    'user_id' => $user->id,
                    'showtime_id' => $showtime->id,
                    'total_amount' => 0,
                    'status' => BookingStatus::Pending,
                    'expires_at' => now()->addMinutes(self::HOLD_MINUTES),
                ]);

                $total = 0;
                foreach ($seats as $seat) {
                    $price = $showtime->base_price + $seat->type->surcharge();
                    $booking->seats()->create([
                        'showtime_id' => $showtime->id,
                        'active_showtime_id' => $showtime->id,
                        'seat_id' => $seat->id,
                        'price' => $price,
                    ]);
                    $total += $price;
                }

                $booking->update(['total_amount' => $total]);

                return $booking;
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'seats' => 'Một hoặc nhiều ghế bạn chọn vừa có người đặt. Vui lòng chọn ghế khác.',
            ]);
        }
    }

    /** Thanh toán giả lập (bản cơ bản). Có thể thay bằng VNPay/MoMo ở bản mở rộng. */
    public function pay(Booking $booking, string $method): Booking
    {
        $this->expireStale();
        $booking->refresh();

        if ($booking->status !== BookingStatus::Pending) {
            throw ValidationException::withMessages(['payment' => 'Đơn này không còn ở trạng thái chờ thanh toán.']);
        }

        return DB::transaction(function () use ($booking, $method) {
            $booking->payments()->create([
                'method' => $method,
                'amount' => $booking->total_amount,
                'status' => PaymentStatus::Success,
                'transaction_code' => 'MOCK-'.strtoupper(Str::random(12)),
                'paid_at' => now(),
            ]);

            $booking->update(['status' => BookingStatus::Paid, 'paid_at' => now()]);

            return $booking;
        });
    }

    public function canCustomerCancel(Booking $booking): bool
    {
        return match ($booking->status) {
            BookingStatus::Pending => true,
            BookingStatus::Paid => $booking->showtime->start_time->gt(now()->addHours(self::CANCEL_BEFORE_HOURS)),
            default => false,
        };
    }

    public function cancel(Booking $booking): void
    {
        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Paid], true)) {
            throw ValidationException::withMessages(['booking' => 'Không thể hủy đơn ở trạng thái hiện tại.']);
        }

        // Hook updated() trong model Booking sẽ nhả ghế
        $booking->update(['status' => BookingStatus::Cancelled]);
    }

    /** Nhân viên soát vé. */
    public function checkIn(Booking $booking, User $staff): void
    {
        if ($booking->status !== BookingStatus::Paid) {
            throw ValidationException::withMessages(['code' => 'Vé không hợp lệ: '.$booking->status->label().'.']);
        }

        if ($booking->showtime->end_time->isPast()) {
            throw ValidationException::withMessages(['code' => 'Suất chiếu của vé này đã kết thúc.']);
        }

        $booking->update([
            'status' => BookingStatus::Used,
            'checked_in_at' => now(),
            'checked_in_by' => $staff->id,
        ]);
    }
}
