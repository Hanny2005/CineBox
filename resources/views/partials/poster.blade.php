{{-- @include('partials.poster', ['movie' => $movie]) --}}
<div class="poster">
    @if ($movie->poster)
        <img src="{{ $movie->poster }}" alt="{{ $movie->title }}" loading="lazy">
    @else
        <div class="poster-placeholder" style="--h: {{ crc32($movie->title) % 360 }}">{{ $movie->title }}</div>
    @endif
    <span class="badge bg-dark border age-badge">{{ $movie->age_rating }}</span>
</div>
