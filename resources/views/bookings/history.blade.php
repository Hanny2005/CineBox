@extends('layouts.app')
@section('title', 'Vé của tôi')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h3 class="mb-0"><i class="bi bi-ticket-perforated text-cine"></i> Vé của tôi</h3>
        <form class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @forelse ($bookings as $b)
        <a href="{{ route('bookings.show', $b) }}" class="card mb-2 text-decoration-none">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <div class="fw-bold">{{ $b->showtime->movie->title }}</div>
                    <div class="small text-secondary">
                        {{ $b->showtime->room->cinema->name }} · {{ $b->showtime->room->name }} ·
                        {{ $b->showtime->start_time->format('H:i d/m/Y') }} · Ghế {{ $b->seats->map(fn ($s) => $s->seat->label)->join(', ') }}
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-{{ $b->status->color() }}">{{ $b->status->label() }}</span>
                    <div class="small mt-1"><code>{{ $b->code }}</code> · @money($b->total_amount)</div>
                </div>
            </div>
        </a>
    @empty
        <div class="text-center text-secondary py-5">
            <i class="bi bi-ticket fs-1 d-block mb-2"></i>Bạn chưa có vé nào.
            <div class="mt-3"><a href="{{ route('movies.index') }}" class="btn btn-cine">Xem phim đang chiếu</a></div>
        </div>
    @endforelse

    <div class="mt-3">{{ $bookings->links() }}</div>
</div>
@endsection
