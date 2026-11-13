@extends('layouts.admin')
@section('title', 'Rạp chiếu')
@section('actions')<a href="{{ route('admin.cinemas.create') }}" class="btn btn-cine"><i class="bi bi-plus-lg"></i> Thêm rạp</a>@endsection

@section('content')
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Tên rạp</th><th>Địa chỉ</th><th>Thành phố</th><th>Điện thoại</th><th>Số phòng</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
    @forelse ($cinemas as $c)
        <tr>
            <td><strong>{{ $c->name }}</strong></td><td>{{ $c->address }}</td><td>{{ $c->city }}</td><td>{{ $c->phone }}</td>
            <td><a href="{{ route('admin.rooms.index', ['cinema_id' => $c->id]) }}">{{ $c->rooms_count }} phòng</a></td>
            <td>{!! $c->is_active ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-secondary">Ngừng</span>' !!}</td>
            <td class="text-end text-nowrap">
                <a href="{{ route('admin.cinemas.edit', $c) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('admin.cinemas.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Xóa rạp này?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-secondary">Chưa có rạp.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endsection
