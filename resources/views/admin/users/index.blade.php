@extends('layouts.admin')
@section('title', 'Tài khoản người dùng')

@section('content')
<form class="row g-2 mb-3">
    <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Tên hoặc email..."></div>
    <div class="col-md-3"><select name="role" class="form-select"><option value="">Tất cả vai trò</option>
        @foreach ($roles as $r)<option value="{{ $r->value }}" @selected(request('role') === $r->value)>{{ $r->label() }}</option>@endforeach</select></div>
    <div class="col-md-2"><button class="btn btn-outline-light w-100">Lọc</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Họ tên</th><th>Email</th><th>Điện thoại</th><th>Số đơn</th><th>Ngày tạo</th><th style="width: 260px">Vai trò</th></tr></thead>
    <tbody>
    @foreach ($users as $u)
        <tr>
            <td>{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ $u->phone }}</td><td>{{ $u->bookings_count }}</td>
            <td class="small">{{ $u->created_at->format('d/m/Y') }}</td>
            <td>
                <form method="POST" action="{{ route('admin.users.role', $u) }}" class="d-flex gap-2">
                    @csrf @method('PUT')
                    <select name="role" class="form-select form-select-sm" @disabled($u->is(auth()->user()))>
                        @foreach ($roles as $r)<option value="{{ $r->value }}" @selected($u->role === $r)>{{ $r->label() }}</option>@endforeach
                    </select>
                    @unless ($u->is(auth()->user()))<button class="btn btn-sm btn-outline-light">Lưu</button>@endunless
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
