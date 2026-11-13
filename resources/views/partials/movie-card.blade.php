<div class="movie-card h-100">
    <a href="{{ route('movies.show', $movie) }}">
        @include('partials.poster', ['movie' => $movie])
        <h6 class="mt-2 mb-1 text-truncate" title="{{ $movie->title }}">{{ $movie->title }}</h6>
    </a>
    <div class="small text-secondary">
        {{ $movie->categories->pluck('name')->join(', ') ?: 'Chưa phân loại' }} · {{ $movie->duration }} phút
    </div>
    @if ($movie->status === \App\Enums\MovieStatus::NowShowing)
        <a href="{{ route('movies.show', $movie) }}#lich-chieu" class="btn btn-cine btn-sm w-100 mt-2"><i class="bi bi-ticket-perforated"></i> Mua vé</a>
    @elseif ($movie->release_date)
        <div class="small text-info mt-2"><i class="bi bi-calendar-event"></i> Khởi chiếu {{ $movie->release_date->format('d/m/Y') }}</div>
    @endif
</div>
