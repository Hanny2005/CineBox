<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    public function index()
    {
        return view('admin.cinemas.index', ['cinemas' => Cinema::withCount('rooms')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.cinemas.form', ['cinema' => new Cinema(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        Cinema::create($this->validated($request));

        return redirect()->route('admin.cinemas.index')->with('success', 'Đã thêm rạp.');
    }

    public function edit(Cinema $cinema)
    {
        return view('admin.cinemas.form', compact('cinema'));
    }

    public function update(Request $request, Cinema $cinema)
    {
        $cinema->update($this->validated($request));

        return redirect()->route('admin.cinemas.index')->with('success', 'Đã cập nhật rạp.');
    }

    public function destroy(Cinema $cinema)
    {
        if ($cinema->showtimes()->exists()) {
            return back()->with('error', 'Rạp đã có lịch chiếu, không thể xóa. Hãy chuyển sang "Ngừng hoạt động".');
        }
        $cinema->delete();

        return back()->with('success', 'Đã xóa rạp.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
