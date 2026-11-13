@extends('layouts.admin')
@section('title', 'Quản lý phim')
@section('actions')<a href="{{ route('admin.movies.create') }}" class="btn btn-cine"><i class="bi bi-plus-lg"></i> Thêm phim</a>@endsection

@section('content')
<form class="row g-2 mb-3">
    <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo tên phim..."></div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Tất cả trạng thái</option>
            @foreach ($statuses as $s)<option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-light w-100">Lọc</button></div>
</form>

<div class="card"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th style="width:60px"></th><th>Tên phim</th><th>Thể loại</th><th>Thời lượng</th><th>Khởi chiếu</th><th>Trạng thái</th><th>Suất chiếu</th><th></th></tr></thead>
        <tbody>
        @forelse ($movies as $movie)
            <tr>
                <td style="width:60px">@include('partials.poster', ['movie' => $movie])</td>
                <td><strong>{{ $movie->title }}</strong> @if ($movie->is_featured)<i class="bi bi-star-fill text-warning" title="Nổi bật"></i>@endif
                    <div class="small text-secondary">{{ $movie->director }}</div></td>
                <td class="small">{{ $movie->categories->pluck('name')->join(', ') }}</td>
                <td>{{ $movie->duration }}'</td>
                <td>{{ $movie->release_date?->format('d/m/Y') }}</td>
                <td><span class="badge bg-{{ $movie->status->color() }}">{{ $movie->status->label() }}</span></td>
                <td>{{ $movie->showtimes_count }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('admin.showtimes.create', ['movie_id' => $movie->id]) }}" class="btn btn-sm btn-outline-info" title="Thêm suất chiếu"><i class="bi bi-calendar-plus"></i></a>
                    <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.movies.destroy', $movie) }}" class="d-inline" onsubmit="return confirm('Xóa phim này?')">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-secondary py-4">Chưa có phim nào.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-3">{{ $movies->links() }}</div>
@endsection
