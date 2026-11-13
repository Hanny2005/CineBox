<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SeatType;
use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with('cinema')
            ->withCount(['seats' => fn ($q) => $q->where('is_active', true)])
            ->when($request->filled('cinema_id'), fn ($q) => $q->where('cinema_id', $request->cinema_id))
            ->orderBy('cinema_id')->orderBy('name')
            ->get();

        return view('admin.rooms.index', ['rooms' => $rooms, 'cinemas' => Cinema::orderBy('name')->get()]);
    }

    public function create()
    {
        return $this->form(new Room(['format' => '2D', 'total_rows' => 8, 'seats_per_row' => 12, 'is_active' => true]));
    }

    public function store(Request $request)
    {
        $room = DB::transaction(function () use ($request) {
            $room = Room::create($this->validated($request));
            $room->generateSeats();

            return $room;
        });

        return redirect()->route('admin.rooms.seats', $room)->with('success', 'Đã tạo phòng và sinh '.$room->seats()->count().' ghế. Bạn có thể chỉnh loại ghế bên dưới.');
    }

    public function edit(Room $room)
    {
        return $this->form($room);
    }

    public function update(Request $request, Room $room)
    {
        $data = $this->validated($request, $room);
        $layoutChanged = $data['total_rows'] != $room->total_rows || $data['seats_per_row'] != $room->seats_per_row;

        if ($layoutChanged && $room->hasShowtimes()) {
            return back()->withInput()->with('error', 'Phòng đã có lịch chiếu nên không thể đổi số hàng/số ghế. Hãy chỉnh từng ghế trong "Sơ đồ ghế".');
        }

        DB::transaction(function () use ($room, $data, $layoutChanged) {
            $room->update($data);
            if ($layoutChanged) {
                $room->generateSeats();
            }
        });

        return redirect()->route('admin.rooms.index')->with('success', 'Đã cập nhật phòng.');
    }

    public function destroy(Room $room)
    {
        if ($room->hasShowtimes()) {
            return back()->with('error', 'Phòng đã có lịch chiếu, không thể xóa.');
        }
        $room->delete();

        return back()->with('success', 'Đã xóa phòng.');
    }

    public function seats(Room $room)
    {
        $room->load('cinema', 'seats');

        return view('admin.rooms.seats', ['room' => $room, 'rows' => $room->seats->groupBy('row_label')]);
    }

    /** Nhận seats[id] = standard | vip | couple | off */
    public function updateSeats(Request $request, Room $room)
    {
        $data = $request->validate([
            'seats' => ['required', 'array'],
            'seats.*' => [Rule::in(['standard', 'vip', 'couple', 'off'])],
        ]);

        DB::transaction(function () use ($room, $data) {
            $seats = $room->seats()->get()->keyBy('id');
            foreach ($data['seats'] as $id => $value) {
                if (! $seat = $seats->get((int) $id)) {
                    continue;
                }
                $seat->is_active = $value !== 'off';
                if ($value !== 'off') {
                    $seat->type = SeatType::from($value);
                }
                $seat->save();
            }
        });

        return back()->with('success', 'Đã lưu sơ đồ ghế.');
    }

    private function form(Room $room)
    {
        return view('admin.rooms.form', [
            'room' => $room,
            'cinemas' => Cinema::orderBy('name')->get(),
            'locked' => $room->exists && $room->hasShowtimes(),
        ]);
    }

    private function validated(Request $request, ?Room $room = null): array
    {
        $data = $request->validate([
            'cinema_id' => ['required', 'exists:cinemas,id'],
            'name' => ['required', 'string', 'max:50',
                Rule::unique('rooms')->where('cinema_id', $request->cinema_id)->ignore($room?->id)],
            'format' => ['required', Rule::in(['2D', '3D', 'IMAX'])],
            'total_rows' => ['required', 'integer', 'min:1', 'max:26'],
            'seats_per_row' => ['required', 'integer', 'min:1', 'max:30'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
