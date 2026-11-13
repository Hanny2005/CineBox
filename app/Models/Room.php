<?php

namespace App\Models;

use App\Enums\SeatType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['cinema_id', 'name', 'format', 'total_rows', 'seats_per_row', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function cinema(): BelongsTo
    {
        return $this->belongsTo(Cinema::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class)->orderBy('row_label')->orderBy('number');
    }

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }

    /**
     * Sinh lại toàn bộ ghế theo số hàng/số ghế mỗi hàng.
     * Mặc định: hàng cuối là ghế đôi, khoảng giữa phía sau là VIP, còn lại ghế thường.
     */
    public function generateSeats(): void
    {
        $this->seats()->delete();

        $vipFrom = intdiv($this->total_rows, 3);
        $vipTo = $this->total_rows - 2;
        $now = now();
        $data = [];

        for ($i = 0; $i < $this->total_rows; $i++) {
            $label = chr(ord('A') + $i);
            $isCoupleRow = $this->total_rows >= 4 && $i === $this->total_rows - 1;
            $count = $isCoupleRow ? intdiv($this->seats_per_row, 2) : $this->seats_per_row;
            $type = match (true) {
                $isCoupleRow => SeatType::Couple,
                $this->total_rows >= 4 && $i >= $vipFrom && $i <= $vipTo => SeatType::Vip,
                default => SeatType::Standard,
            };

            for ($n = 1; $n <= $count; $n++) {
                $data[] = [
                    'room_id' => $this->id, 'row_label' => $label, 'number' => $n,
                    'type' => $type->value, 'is_active' => true,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }

        Seat::insert($data);
    }

    public function hasShowtimes(): bool
    {
        return $this->showtimes()->exists();
    }
}
