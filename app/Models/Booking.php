<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'showtime_id', 'total_amount', 'status',
        'expires_at', 'paid_at', 'checked_in_at', 'checked_in_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            $booking->code ??= static::generateCode();
        });

        // Hủy / hết hạn => nhả ghế; giữ nguyên các dòng booking_seats để còn lịch sử
        static::updated(function (Booking $booking) {
            if ($booking->wasChanged('status')) {
                $booking->seats()->update([
                    'active_showtime_id' => $booking->status->holdsSeats() ? $booking->showtime_id : null,
                ]);
            }
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
