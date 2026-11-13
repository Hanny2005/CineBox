@extends('layouts.admin')
@section('title', 'Phòng chiếu & sơ đồ ghế')
@section('actions')<a href="{{ route('admin.rooms.create') }}" class="btn btn-cine"><i class="bi bi-plus-lg"></i> Thêm phòng</a>@endsection

@section('content')
<form class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="cinema_id" class="form-select" onchange="this.form.submit()">
            <option value="">Tất cả rạp</option>
            @foreach ($cinemas as $c)<option value="{{ $c->id }}" @selected(request('cinema_id') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
    </div>
</form>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Rạp</th><th>Phòng</th><th>Định dạng</th><th>Bố cục</th><th>Ghế hoạt động</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
    @forelse ($rooms as $r)
        <tr>
            <td>{{ $r->cinema->name }}</td><td><strong>{{ $r->name }}</strong></td><td>{{ $r->format }}</td>
            <td>{{ $r->total_rows }} hàng × {{ $r->seats_per_row }} ghế</td><td>{{ $r->seats_count }}</td>
            <td>{!! $r->is_active ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-secondary">Ngừng</span>' !!}</td>
            <td class="text-end text-nowrap">
                <a href="{{ route('admin.rooms.seats', $r) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-grid-3x3"></i> Sơ đồ ghế</a>
                <a href="{{ route('admin.rooms.edit', $r) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('admin.rooms.destroy', $r) }}" class="d-inline" onsubmit="return confirm('Xóa phòng này?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-secondary">Chưa có phòng chiếu.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endsection
