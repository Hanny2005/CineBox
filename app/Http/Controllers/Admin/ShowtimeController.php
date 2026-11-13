<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ShowtimeController extends Controller
{
    /** Thời gian dọn phòng giữa 2 suất (phút) */
    private const CLEANUP_MINUTES = 15;

    public function index(Request $request)
    {
        $date = Carbon::parse($request->query('date', today()->toDateString()));

        $showtimes = Showtime::with('movie', 'room.cinema')
            ->withCount(['bookings as sold_count' => fn ($q) => $q->whereIn('status', ['paid', 'used'])])
            ->whereDate('start_time', $date)
            ->when($request->filled('cinema_id'), fn ($q) => $q->whereHas('room', fn ($r) => $r->where('cinema_id', $request->cinema_id)))
            ->when($request->filled('movie_id'), fn ($q) => $q->where('movie_id', $request->movie_id))
            ->orderBy('start_time')
            ->paginate(30)
            ->withQueryString();

        return view('admin.showtimes.index', [
            'showtimes' => $showtimes,
            'date' => $date,
            'cinemas' => Cinema::orderBy('name')->get(),
            'movies' => Movie::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function create(Request $request)
    {
        return $this->form(new Showtime([
            'movie_id' => $request->query('movie_id'),
            'start_time' => today()->addDay()->setTime(19, 0),
            'base_price' => 75000,
            'is_active' => true,
        ]));
    }

    public function store(Request $request)
    {
        Showtime::create($this->validated($request));

        return redirect()->route('admin.showtimes.index', ['date' => $request->date('start_time')->toDateString()])
            ->with('success', 'Đã thêm suất chiếu.');
    }

    public function edit(Showtime $showtime)
    {
        return $this->form($showtime);
    }

    public function update(Request $request, Showtime $showtime)
    {
        $data = $this->validated($request, $showtime);
        $hasBookings = $showtime->bookings()->exists();

        if ($hasBookings && ($data['room_id'] != $showtime->room_id || $data['movie_id'] != $showtime->movie_id)) {
            return back()->withInput()->with('error', 'Suất chiếu đã có người đặt, không thể đổi phim hoặc phòng.');
        }

        $showtime->update($data);

        return redirect()->route('admin.showtimes.index', ['date' => $showtime->start_time->toDateString()])
            ->with('success', 'Đã cập nhật suất chiếu.');
    }

    public function destroy(Showtime $showtime)
    {
        if ($showtime->bookings()->exists()) {
            $showtime->update(['is_active' => false]);

            return back()->with('success', 'Suất chiếu đã có đơn đặt nên được chuyển sang "Ngừng bán" thay vì xóa.');
        }
        $showtime->delete();

        return back()->with('success', 'Đã xóa suất chiếu.');
    }

    private function form(Showtime $showtime)
    {
        return view('admin.showtimes.form', [
            'showtime' => $showtime,
            'movies' => Movie::where('status', '!=', 'ended')->orderBy('title')->get(),
            'rooms' => Room::with('cinema')->where('is_active', true)->orderBy('cinema_id')->orderBy('name')->get(),
        ]);
    }

    private function validated(Request $request, ?Showtime $showtime = null): array
    {
        $data = $request->validate([
            'movie_id' => ['required', 'exists:movies,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'start_time' => ['required', 'date'],
            'base_price' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $movie = Movie::findOrFail($data['movie_id']);
        $start = Carbon::parse($data['start_time']);
        $end = $start->copy()->addMinutes($movie->duration + self::CLEANUP_MINUTES);

        if (Showtime::overlaps((int) $data['room_id'], $start, $end, $showtime?->id)) {
            throw ValidationException::withMessages(['start_time' => 'Phòng này đã có suất chiếu khác trong khoảng '.$start->format('H:i').' – '.$end->format('H:i').'.']);
        }

        return $data + ['start_time' => $start, 'end_time' => $end, 'is_active' => $request->boolean('is_active')];
    }
}
