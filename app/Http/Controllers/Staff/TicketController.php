<?php

namespace App\Http\Controllers\Staff;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request, BookingService $service)
    {
        $service->expireStale();
        $booking = null;

        if ($request->filled('code')) {
            $booking = Booking::with('user', 'showtime.movie', 'showtime.room.cinema', 'seats.seat', 'checkedInBy')
                ->where('code', strtoupper(trim($request->code)))
                ->first();

            if (! $booking) {
                session()->now('error', 'Không tìm thấy vé với mã '.$request->code.'.');
            }
        }

        $recent = Booking::with('showtime.movie', 'user')
            ->where('status', BookingStatus::Used)
            ->whereDate('checked_in_at', today())
            ->latest('checked_in_at')
            ->take(10)
            ->get();

        return view('staff.tickets', compact('booking', 'recent'));
    }

    public function checkIn(Request $request, Booking $booking, BookingService $service)
    {
        $booking->load('showtime');
        $service->checkIn($booking, $request->user());

        return redirect()->route('staff.tickets.index', ['code' => $booking->code])
            ->with('success', 'Vé '.$booking->code.' hợp lệ. Đã xác nhận vào rạp.');
    }
}
