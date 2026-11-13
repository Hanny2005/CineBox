<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request, BookingService $service)
    {
        $service->expireStale();

        $bookings = Booking::with('user', 'showtime.movie', 'showtime.room.cinema')
            ->withCount('seats')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->q);
                $q->where(fn ($w) => $w->where('code', strtoupper($term))
                    ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($term).'%'])));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.bookings.index', ['bookings' => $bookings, 'statuses' => BookingStatus::cases()]);
    }

    public function show(Booking $booking)
    {
        $booking->load('user', 'showtime.movie', 'showtime.room.cinema', 'seats.seat', 'payments', 'checkedInBy');

        return view('admin.bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking, BookingService $service)
    {
        $service->cancel($booking);

        return back()->with('success', 'Đã hủy đơn '.$booking->code.'.');
    }
}
