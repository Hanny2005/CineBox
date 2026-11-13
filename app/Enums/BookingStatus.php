<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';     // đang giữ ghế, chờ thanh toán
    case Paid = 'paid';           // đã thanh toán, vé hợp lệ
    case Used = 'used';           // nhân viên đã soát vé
    case Cancelled = 'cancelled'; // khách/admin hủy
    case Expired = 'expired';     // quá hạn giữ ghế mà chưa thanh toán

    /** Các trạng thái vẫn đang chiếm ghế. */
    public function holdsSeats(): bool
    {
        return in_array($this, [self::Pending, self::Paid, self::Used], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ thanh toán',
            self::Paid => 'Đã thanh toán',
            self::Used => 'Đã sử dụng',
            self::Cancelled => 'Đã hủy',
            self::Expired => 'Hết hạn',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Used => 'primary',
            self::Cancelled => 'danger',
            self::Expired => 'secondary',
        };
    }
}
