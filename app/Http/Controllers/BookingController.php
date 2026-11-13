<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Showtime;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $service)
    {
    }

    public function seats(Showtime $showtime)
    {
        abort_unless($showtime->is_active && $showtime->start_time->isFuture(), 404, 'Suất chiếu không còn mở bán.');

        $this->service->expireStale();
        $showtime->load('movie', 'room.cinema', 'room.seats');

        return view('bookings.seats', [
            'showtime' => $showtime,
            'rows' => $showtime->room->seats->groupBy('row_label'),
            'taken' => $this->service->takenSeatIds($showtime),
            'maxSeats' => BookingService::MAX_SEATS,
        ]);
    }

    public function store(Request $request, Showtime $showtime)
    {
        $data = $request->validate([
            'seats' => ['required', 'array', 'min:1', 'max:'.BookingService::MAX_SEATS],
            'seats.*' => ['integer'],
        ], ['seats.required' => 'Vui lòng chọn ít nhất 1 ghế.']);

        $booking = $this->service->create($request->user(), $showtime, $data['seats']);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Đã giữ ghế trong '.BookingService::HOLD_MINUTES.' phút. Vui lòng thanh toán để hoàn tất.');
    }

    public function show(Request $request, Booking $booking)
    {
        $this->authorizeOwner($request, $booking);
        $this->service->expireStale();
        $booking->refresh()->load('showtime.movie', 'showtime.room.cinema', 'seats.seat', 'payments');

        return view('bookings.show', [
            'booking' => $booking,
            'canCancel' => $this->service->canCustomerCancel($booking),
        ]);
    }

    public function pay(Request $request, Booking $booking)
    {
        $this->authorizeOwner($request, $booking);
        $data = $request->validate(['method' => ['required', 'in:mock,cash']]);

        $this->service->pay($booking, $data['method']);

        return redirect()->route('bookings.show', $booking)->with('success', 'Thanh toán thành công! Đưa mã vé cho nhân viên khi vào rạp.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        $this->authorizeOwner($request, $booking);
        $booking->load('showtime');

        abort_unless($this->service->canCustomerCancel($booking), 422,
            'Chỉ được hủy vé trước giờ chiếu '.BookingService::CANCEL_BEFORE_HOURS.' tiếng.');

        $this->service->cancel($booking);

        return redirect()->route('bookings.show', $booking)->with('success', 'Đã hủy đơn đặt vé.');
    }

    public function history(Request $request)
    {
        $this->service->expireStale();

        $bookings = $request->user()->bookings()
            ->with('showtime.movie', 'showtime.room.cinema', 'seats.seat')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bookings.history', ['bookings' => $bookings, 'statuses' => BookingStatus::cases()]);
    }

    private function authorizeOwner(Request $request, Booking $booking): void
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->isStaff(), 403);
    }
}
