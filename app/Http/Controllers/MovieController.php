<?php

namespace App\Http\Controllers;

use App\Enums\MovieStatus;
use App\Models\Category;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', MovieStatus::NowShowing->value);

        $movies = Movie::with('categories')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->filled('q'), fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($request->q).'%']))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('slug', $request->category)))
            ->latest('release_date')
            ->paginate(12)
            ->withQueryString();

        return view('movies.index', [
            'movies' => $movies,
            'categories' => Category::orderBy('name')->get(),
            'status' => $status,
        ]);
    }

    public function show(Request $request, Movie $movie)
    {
        $movie->load('categories');

        // 7 ngày tới, mặc định ngày đầu tiên có suất chiếu
        $days = collect(range(0, 6))->map(fn ($i) => Carbon::today()->addDays($i));
        $date = Carbon::parse($request->query('date', today()->toDateString()))->startOfDay();
        if ($date->lt(today()) || $date->gt(today()->addDays(6))) {
            $date = today();
        }

        $showtimes = $movie->showtimes()
            ->with('room.cinema')
            ->where('is_active', true)
            ->whereBetween('start_time', [max($date, now()), $date->copy()->endOfDay()])
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($s) => $s->room->cinema->name);

        return view('movies.show', compact('movie', 'days', 'date', 'showtimes'));
    }
}
