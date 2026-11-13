<?php

namespace App\Enums;

enum SeatType: string
{
    case Standard = 'standard';
    case Vip = 'vip';
    case Couple = 'couple';

    /** Phụ thu (VNĐ) cộng thêm vào giá gốc của suất chiếu. */
    public function surcharge(): int
    {
        return match ($this) {
            self::Standard => 0,
            self::Vip => 15000,
            self::Couple => 40000,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Ghế thường',
            self::Vip => 'Ghế VIP',
            self::Couple => 'Ghế đôi',
        };
    }
}
