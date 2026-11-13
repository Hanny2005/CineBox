@extends('layouts.app')
@section('title', 'Soát vé')

@section('content')
<div class="container" style="max-width: 900px">
    <h3 class="mb-3"><i class="bi bi-qr-code-scan text-cine"></i> Soát vé</h3>
    <form class="input-group input-group-lg mb-4">
        <input name="code" value="{{ request('code') }}" class="form-control text-uppercase" placeholder="Nhập hoặc quét mã vé (VD: K7Q2M9XA1B)" autofocus autocomplete="off">
        <button class="btn btn-cine"><i class="bi bi-search"></i> Kiểm tra</button>
    </form>

    @if ($booking)
        @php($st = $booking->showtime)
        <div class="card mb-4 border-{{ $booking->status->color() }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="ticket-code">{{ $booking->code }}</div>
                        <h4 class="mt-2">{{ $st->movie->title }}</h4>
                    </div>
                    <span class="badge fs-6 bg-{{ $booking->status->color() }}">{{ $booking->status->label() }}</span>
                </div>
                <div class="row small g-3 my-2">
                    <div class="col-md-4"><div class="text-secondary">Khách hàng</div><strong>{{ $booking->user->name }}</strong><div>{{ $booking->user->phone }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Suất chiếu</div><strong>{{ $st->start_time->format('H:i d/m/Y') }}</strong><div>{{ $st->room->cinema->name }} · {{ $st->room->name }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Ghế</div><strong class="fs-5 text-cine">{{ $booking->seats->map(fn ($s) => $s->seat->label)->join(', ') }}</strong></div>
                </div>
                @if ($booking->status === \App\Enums\BookingStatus::Paid)
                    <form method="POST" action="{{ route('staff.tickets.checkin', $booking) }}">
                        @csrf
                        <button class="btn btn-success btn-lg"><i class="bi bi-check2-circle"></i> Xác nhận vào rạp</button>
                    </form>
                @elseif ($booking->status === \App\Enums\BookingStatus::Used)
                    <div class="alert alert-info mb-0">Vé đã được soát lúc {{ $booking->checked_in_at->format('H:i d/m/Y') }} bởi {{ $booking->checkedInBy?->name ?? '—' }}.</div>
                @else
                    <div class="alert alert-danger mb-0">Vé không hợp lệ để vào rạp ({{ $booking->status->label() }}).</div>
                @endif
            </div>
        </div>
    @endif

    <h5>Vé đã soát hôm nay</h5>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>Giờ soát</th><th>Mã vé</th><th>Phim</th><th>Khách</th></tr></thead>
            <tbody>
            @forelse ($recent as $r)
                <tr><td>{{ $r->checked_in_at->format('H:i') }}</td><td><code>{{ $r->code }}</code></td><td>{{ $r->showtime->movie->title }}</td><td>{{ $r->user->name }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-secondary">Chưa có vé nào được soát hôm nay.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
