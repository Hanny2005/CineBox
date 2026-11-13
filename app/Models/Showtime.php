<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Showtime extends Model
{
    protected $fillable = ['movie_id', 'room_id', 'start_time', 'end_time', 'base_price', 'is_active'];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** Các ghế đang bị chiếm (đang giữ hoặc đã bán) trong suất chiếu này. */
    public function takenSeats(): HasMany
    {
        return $this->hasMany(BookingSeat::class, 'active_showtime_id');
    }

    public function scopeUpcoming(Builder $q): void
    {
        $q->where('is_active', true)->where('start_time', '>', now());
    }

    /** Kiểm tra trùng giờ khi admin tạo/sửa lịch chiếu trong cùng một phòng. */
    public static function overlaps(int $roomId, $start, $end, ?int $ignoreId = null): bool
    {
        return static::where('room_id', $roomId)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();
    }
}
