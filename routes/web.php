<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\TicketController;
use Illuminate\Support\Facades\Route;

// ---------- Khách vãng lai ----------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/phim', [MovieController::class, 'index'])->name('movies.index');
Route::get('/phim/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register']);
});

// ---------- Khách hàng đã đăng nhập ----------
Route::middleware('auth')->group(function () {
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');

    Route::get('/suat-chieu/{showtime}/chon-ghe', [BookingController::class, 'seats'])->name('bookings.seats');
    Route::post('/suat-chieu/{showtime}/dat-ve', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/ve-cua-toi', [BookingController::class, 'history'])->name('bookings.history');
    Route::get('/ve/{booking:code}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/ve/{booking:code}/thanh-toan', [BookingController::class, 'pay'])->name('bookings.pay');
    Route::post('/ve/{booking:code}/huy', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/tai-khoan', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/tai-khoan', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/tai-khoan/mat-khau', [ProfileController::class, 'password'])->name('profile.password');
});

// ---------- Nhân viên (admin cũng vào được) ----------
Route::middleware(['auth', 'role:staff,admin'])->prefix('nhan-vien')->name('staff.')->group(function () {
    Route::get('/soat-ve', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/soat-ve/{booking:code}', [TicketController::class, 'checkIn'])->name('tickets.checkin');
});

// ---------- Quản trị ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('movies', Admin\MovieController::class)->except('show');
    Route::resource('categories', Admin\CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('cinemas', Admin\CinemaController::class)->except('show');
    Route::resource('rooms', Admin\RoomController::class)->except('show');
    Route::get('rooms/{room}/seats', [Admin\RoomController::class, 'seats'])->name('rooms.seats');
    Route::put('rooms/{room}/seats', [Admin\RoomController::class, 'updateSeats'])->name('rooms.seats.update');
    Route::resource('showtimes', Admin\ShowtimeController::class)->except('show');

    Route::get('bookings', [Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{booking}', [Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::post('bookings/{booking}/cancel', [Admin\BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::put('users/{user}/role', [Admin\UserController::class, 'updateRole'])->name('users.role');
});
