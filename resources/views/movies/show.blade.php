@extends('layouts.app')
@section('title', $movie->title)

@section('content')
<div class="container">
    <div class="backdrop-wrap mb-5 p-4">
    @if ($movie->poster)<div class="backdrop" style="background-image: url('{{ $movie->poster }}')"></div>@endif
    <div class="row g-4 backdrop-content">
        <div class="col-6 col-md-3 mx-auto">@include('partials.poster', ['movie' => $movie])</div>
        <div class="col-md-9">
            <span class="badge bg-{{ $movie->status->color() }} mb-2">{{ $movie->status->label() }}</span>
            <h1 class="fw-bold">{{ $movie->title }}</h1>
            <dl class="row mb-3">
                <dt class="col-sm-3 text-secondary">Thể loại</dt><dd class="col-sm-9">{{ $movie->categories->pluck('name')->join(', ') ?: '—' }}</dd>
                <dt class="col-sm-3 text-secondary">Thời lượng</dt><dd class="col-sm-9">{{ $movie->duration }} phút</dd>
                <dt class="col-sm-3 text-secondary">Đạo diễn</dt><dd class="col-sm-9">{{ $movie->director ?: '—' }}</dd>
                <dt class="col-sm-3 text-secondary">Diễn viên</dt><dd class="col-sm-9">{{ $movie->cast ?: '—' }}</dd>
                <dt class="col-sm-3 text-secondary">Khởi chiếu</dt><dd class="col-sm-9">{{ $movie->release_date?->format('d/m/Y') ?? '—' }}</dd>
                <dt class="col-sm-3 text-secondary">Ngôn ngữ</dt><dd class="col-sm-9">{{ $movie->language ?: '—' }}</dd>
                <dt class="col-sm-3 text-secondary">Phân loại</dt><dd class="col-sm-9"><span class="badge border">{{ $movie->age_rating }}</span></dd>
            </dl>
            <p class="text-body-secondary">{{ $movie->description }}</p>
            @if ($movie->trailer_embed_url)
                <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#trailerModal"><i class="bi bi-play-btn"></i> Xem trailer</button>
            @endif
        </div>
    </div>
    </div>

    <h3 id="lich-chieu" class="mb-3"><i class="bi bi-calendar3 text-cine"></i> Lịch chiếu</h3>
    <div class="d-flex gap-2 overflow-auto pb-2 mb-4">
        @foreach ($days as $d)
            <a href="{{ route('movies.show', [$movie, 'date' => $d->toDateString()]) }}#lich-chieu"
               class="btn {{ $d->isSameDay($date) ? 'btn-cine' : 'btn-outline-secondary' }} text-nowrap">
                <div class="small">{{ $d->isToday() ? 'Hôm nay' : ['CN','Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7'][$d->dayOfWeek] }}</div>
                <div class="fw-bold">{{ $d->format('d/m') }}</div>
            </a>
        @endforeach
    </div>

    @forelse ($showtimes as $cinemaName => $items)
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-geo-alt text-cine"></i> {{ $cinemaName }}</h5>
                <p class="small text-secondary">{{ $items->first()->room->cinema->address }}</p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($items as $st)
                        <a href="{{ route('bookings.seats', $st) }}" class="btn btn-outline-light">
                            <div class="fw-bold">{{ $st->start_time->format('H:i') }}</div>
                            <div class="small text-secondary">{{ $st->room->name }} · {{ $st->room->format }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="text-secondary py-4 text-center border rounded">
            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Không có suất chiếu trong ngày {{ $date->format('d/m/Y') }}. Hãy chọn ngày khác.
        </div>
    @endforelse
</div>

@if ($movie->trailer_embed_url)
<div class="modal fade" id="trailerModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-black">
            <div class="ratio ratio-16x9">
                <iframe data-src="{{ $movie->trailer_embed_url }}?autoplay=1" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    const modal = document.getElementById('trailerModal');
    const frame = modal.querySelector('iframe');
    modal.addEventListener('shown.bs.modal', () => frame.src = frame.dataset.src);
    modal.addEventListener('hidden.bs.modal', () => frame.src = '');
</script>
@endpush
@endif
@endsection
