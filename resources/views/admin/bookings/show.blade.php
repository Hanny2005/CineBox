@extends('layouts.admin')
@section('title', 'Đơn '.$booking->code)

@section('content')
@php($st = $booking->showtime)
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card"><div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <div class="ticket-code">{{ $booking->code }}</div>
                <span class="badge fs-6 bg-{{ $booking->status->color() }} align-self-start">{{ $booking->status->label() }}</span>
            </div>
            <dl class="row mb-0">
                <dt class="col-sm-4 text-secondary">Phim</dt><dd class="col-sm-8">{{ $st->movie->title }}</dd>
                <dt class="col-sm-4 text-secondary">Suất chiếu</dt><dd class="col-sm-8">{{ $st->start_time->format('H:i d/m/Y') }} · {{ $st->room->cinema->name }} · {{ $st->room->name }}</dd>
                <dt class="col-sm-4 text-secondary">Khách hàng</dt><dd class="col-sm-8">{{ $booking->user->name }} · {{ $booking->user->email }} · {{ $booking->user->phone }}</dd>
                <dt class="col-sm-4 text-secondary">Đặt lúc</dt><dd class="col-sm-8">{{ $booking->created_at->format('H:i:s d/m/Y') }}</dd>
                <dt class="col-sm-4 text-secondary">Thanh toán lúc</dt><dd class="col-sm-8">{{ $booking->paid_at?->format('H:i:s d/m/Y') ?? '—' }}</dd>
                <dt class="col-sm-4 text-secondary">Soát vé</dt><dd class="col-sm-8">{{ $booking->checked_in_at ? $booking->checked_in_at->format('H:i d/m/Y').' bởi '.($booking->checkedInBy?->name ?? '—') : '—' }}</dd>
            </dl>
        </div></div>
        @if (in_array($booking->status, [\App\Enums\BookingStatus::Pending, \App\Enums\BookingStatus::Paid]))
            <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="mt-3" onsubmit="return confirm('Hủy đơn và mở bán lại ghế?')">
                @csrf<button class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Hủy đơn</button>
            </form>
        @endif
    </div>
    <div class="col-lg-5">
        <div class="card mb-3"><div class="card-body">
            <h6>Ghế</h6>
            <table class="table table-sm mb-0">
                @foreach ($booking->seats as $bs)
                    <tr><td>{{ $bs->seat->label }}</td><td>{{ $bs->seat->type->label() }}</td><td class="text-end">@money($bs->price)</td></tr>
                @endforeach
                <tr class="fw-bold"><td colspan="2">Tổng</td><td class="text-end">@money($booking->total_amount)</td></tr>
            </table>
        </div></div>
        <div class="card"><div class="card-body">
            <h6>Lịch sử thanh toán</h6>
            @forelse ($booking->payments as $p)
                <div class="small border-bottom py-1">{{ $p->paid_at?->format('H:i d/m/Y') }} · {{ strtoupper($p->method) }} · @money($p->amount) · {{ $p->status->value }} · <code>{{ $p->transaction_code }}</code></div>
            @empty
                <div class="small text-secondary">Chưa thanh toán.</div>
            @endforelse
        </div></div>
    </div>
</div>
@endsection
