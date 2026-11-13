<?php

namespace App\Enums;

enum MovieStatus: string
{
    case ComingSoon = 'coming_soon';
    case NowShowing = 'now_showing';
    case Ended = 'ended';

    public function label(): string
    {
        return match ($this) {
            self::ComingSoon => 'Sắp chiếu',
            self::NowShowing => 'Đang chiếu',
            self::Ended => 'Ngừng chiếu',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ComingSoon => 'info',
            self::NowShowing => 'success',
            self::Ended => 'secondary',
        };
    }
}
