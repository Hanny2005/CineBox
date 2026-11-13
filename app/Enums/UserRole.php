<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Staff = 'staff';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Khách hàng',
            self::Staff => 'Nhân viên',
            self::Admin => 'Quản trị viên',
        };
    }
}
