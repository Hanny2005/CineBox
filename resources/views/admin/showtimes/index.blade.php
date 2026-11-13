@extends('layouts.admin')
@section('title', 'Lịch chiếu')
@section('actions')<a href="{{ route('admin.showtimes.create') }}" class="btn btn-cine"><i class="bi bi-plus-lg"></i> Thêm suất chiếu</a>@endsection

@section('content')
<form class="row g-2 mb-3">
    <div class="col-md-3"><input type="date" name="date" value="{{ $date->toDateString() }}" class="form-control"></div>
    <div class="col-md-3"><select name="cinema_id" class="form-select"><option value="">Tất cả rạp</option>
        @foreach ($cinemas as $c)<option value="{{ $c->id }}" @selected(request('cinema_id') == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><select name="movie_id" class="form-select"><option value="">Tất cả phim</option>
        @foreach ($movies as $m)<option value="{{ $m->id }}" @selected(request('movie_id') == $m->id)>{{ $m->title }}</option>@endforeach</select></div>
    <div class="col-md-2"><button class="btn btn-outline-light w-100">Lọc</button></div>
</form>

<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Giờ chiếu</th><th>Phim</th><th>Rạp / Phòng</th><th class="text-end">Giá gốc</th><th>Đơn đã bán</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
    @forelse ($showtimes as $s)
        <tr class="{{ $s->is_active ? '' : 'opacity-50' }}">
            <td><strong>{{ $s->start_time->format('H:i') }}</strong> – {{ $s->end_time->format('H:i') }}</td>
            <td>{{ $s->movie->title }}</td>
            <td>{{ $s->room->cinema->name }} · {{ $s->room->name }}</td>
            <td class="text-end">@money($s->base_price)</td>
            <td>{{ $s->sold_count }}</td>
            <td>{!! $s->is_active ? '<span class="badge bg-success">Mở bán</span>' : '<span class="badge bg-secondary">Ngừng bán</span>' !!}</td>
            <td class="text-end text-nowrap">
                <a href="{{ route('admin.showtimes.edit', $s) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('admin.showtimes.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Xóa suất chiếu này?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-secondary">Không có suất chiếu ngày {{ $date->format('d/m/Y') }}.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $showtimes->links() }}</div>
@endsection
