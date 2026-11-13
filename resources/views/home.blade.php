@extends('layouts.app')
@section('title', 'Trang chủ')

@section('content')
<div class="container">
    @if ($featured->isNotEmpty())
        <div id="heroCarousel" class="carousel slide hero mb-5" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach ($featured as $movie)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        @if ($movie->poster)<div class="backdrop" style="background-image: url('{{ $movie->poster }}')"></div>@endif
                        <div class="row g-4 align-items-center p-4 p-md-5 position-relative">
                            <div class="col-5 col-md-3">@include('partials.poster', ['movie' => $movie])</div>
                            <div class="col-7 col-md-9">
                                <span class="badge bg-danger mb-2">Phim nổi bật</span>
                                <h1 class="display-6 fw-bold">{{ $movie->title }}</h1>
                                <p class="text-secondary mb-2">{{ $movie->categories->pluck('name')->join(' · ') }} · {{ $movie->duration_text }} · {{ $movie->age_rating }}</p>
                                <p class="d-none d-md-block col-lg-8">{{ \Illuminate\Support\Str::limit($movie->description, 220) }}</p>
                                <a href="{{ route('movies.show', $movie) }}#lich-chieu" class="btn btn-cine btn-lg"><i class="bi bi-ticket-perforated"></i> Đặt vé ngay</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($featured->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="width:5%"><span class="carousel-control-prev-icon"></span></button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="width:5%"><span class="carousel-control-next-icon"></span></button>
            @endif
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="bi bi-play-circle text-cine"></i> Phim đang chiếu</h3>
        <a href="{{ route('movies.index') }}" class="link-secondary">Xem tất cả <i class="bi bi-chevron-right"></i></a>
    </div>
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3 mb-5">
        @forelse ($nowShowing as $movie)
            <div class="col">@include('partials.movie-card')</div>
        @empty
            <p class="text-secondary">Chưa có phim đang chiếu.</p>
        @endforelse
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="bi bi-hourglass-split text-info"></i> Phim sắp chiếu</h3>
        <a href="{{ route('movies.index', ['status' => 'coming_soon']) }}" class="link-secondary">Xem tất cả <i class="bi bi-chevron-right"></i></a>
    </div>
    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
        @forelse ($comingSoon as $movie)
            <div class="col">@include('partials.movie-card')</div>
        @empty
            <p class="text-secondary">Chưa có phim sắp chiếu.</p>
        @endforelse
    </div>
</div>
@endsection
