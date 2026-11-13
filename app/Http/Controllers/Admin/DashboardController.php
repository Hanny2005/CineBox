<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\MovieStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $sold = [BookingStatus::Paid->value, BookingStatus::Used->value];

        $stats = [
            'revenue_total' => (int) Booking::whereIn('status', $sold)->sum('total_amount'),
            'revenue_month' => (int) Booking::whereIn('status', $sold)->where('paid_at', '>=', now()->startOfMonth())->sum('total_amount'),
            'tickets_sold' => BookingSeat::whereHas('booking', fn ($q) => $q->whereIn('status', $sold))->count(),
            'bookings_today' => Booking::whereIn('status', $sold)->whereDate('paid_at', today())->count(),
            'movies_showing' => Movie::where('status', MovieStatus::NowShowing)->count(),
            'customers' => User::where('role', 'customer')->count(),
        ];

        // Doanh thu 7 ngày gần nhất
        $raw = DB::table('bookings')
            ->whereIn('status', $sold)
            ->where('paid_at', '>=', today()->subDays(6))
            ->selectRaw('DATE(paid_at) as day, SUM(total_amount) as revenue')
            ->groupByRaw('DATE(paid_at)')
            ->pluck('revenue', 'day');

        $chart = collect(range(6, 0))->map(function ($i) use ($raw) {
            $d = Carbon::today()->subDays($i);

            return ['label' => $d->format('d/m'), 'value' => (int) ($raw[$d->toDateString()] ?? 0)];
        });

        $topMovies = DB::table('bookings')
            ->join('showtimes', 'showtimes.id', '=', 'bookings.showtime_id')
            ->join('movies', 'movies.id', '=', 'showtimes.movie_id')
            ->whereIn('bookings.status', $sold)
            ->groupBy('movies.id', 'movies.title')
            ->select('movies.title', DB::raw('SUM(bookings.total_amount) as revenue'), DB::raw('COUNT(bookings.id) as orders'))
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $latest = Booking::with('user', 'showtime.movie')->latest()->take(8)->get();

        return view('admin.dashboard', compact('stats', 'chart', 'topMovies', 'latest'));
    }
}
