@extends('layouts.app')
@section('title', 'Danh sách phim')

@section('content')
<div class="container">
    <form class="row g-2 align-items-end mb-4">
        <div class="col-12 col-md-4">
            <label class="form-label small text-secondary">Tên phim</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nhập tên phim...">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small text-secondary">Thể loại</label>
            <select name="category" class="form-select">
                <option value="">Tất cả thể loại</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->slug }}" @selected(request('category') === $c->slug)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small text-secondary">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="now_showing" @selected($status === 'now_showing')>Đang chiếu</option>
                <option value="coming_soon" @selected($status === 'coming_soon')>Sắp chiếu</option>
                <option value="all" @selected($status === 'all')>Tất cả</option>
            </select>
        </div>
        <div class="col-12 col-md-2"><button class="btn btn-cine w-100"><i class="bi bi-search"></i> Lọc</button></div>
    </form>

    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
        @forelse ($movies as $movie)
            <div class="col">@include('partials.movie-card')</div>
        @empty
            <div class="col-12 text-center text-secondary py-5"><i class="bi bi-camera-reels fs-1 d-block mb-2"></i>Không tìm thấy phim phù hợp.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $movies->links() }}</div>
</div>
@endsection
