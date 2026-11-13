<?php

use App\Services\BookingService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('bookings:expire', function (BookingService $service) {
    $this->info('Đã hủy '.$service->expireStale().' đơn quá hạn giữ ghế.');
})->purpose('Nhả ghế của các đơn chưa thanh toán đã quá hạn');

Schedule::command('bookings:expire')->everyMinute();
