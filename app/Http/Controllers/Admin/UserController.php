<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::withCount('bookings')
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.mb_strtolower($request->q).'%';
                $q->where(fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', [$term])->orWhereRaw('LOWER(email) LIKE ?', [$term]));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users, 'roles' => UserRole::cases()]);
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', Rule::enum(UserRole::class)]]);

        if ($user->is($request->user())) {
            return back()->with('error', 'Bạn không thể tự đổi quyền của chính mình.');
        }

        $user->forceFill(['role' => $data['role']])->save();

        return back()->with('success', 'Đã đổi quyền của '.$user->name.'.');
    }
}
