@extends('layouts.app')
@section('title', 'Vé '.$booking->code)

@php($st = $booking->showtime)
@php($isPending = $booking->status === \App\Enums\BookingStatus::Pending)
@php($isValid = in_array($booking->status, [\App\Enums\BookingStatus::Paid, \App\Enums\BookingStatus::Used]))

@section('content')
<div class="container" style="max-width: 820px">
    <div class="ticket p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="small text-secondary">Mã đặt vé</div>
                <div class="ticket-code">{{ $booking->code }}</div>
            </div>
            <span class="badge fs-6 bg-{{ $booking->status->color() }}">{{ $booking->status->label() }}</span>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <h3 class="mb-1">{{ $st->movie->title }}</h3>
                <p class="text-secondary">{{ $st->movie->duration }} phút · {{ $st->movie->age_rating }}</p>
                <div class="row small g-3">
                    <div class="col-6"><div class="text-secondary">Rạp</div><strong>{{ $st->room->cinema->name }}</strong></div>
                    <div class="col-6"><div class="text-secondary">Phòng</div><strong>{{ $st->room->name }} ({{ $st->room->format }})</strong></div>
                    <div class="col-6"><div class="text-secondary">Suất chiếu</div><strong>{{ $st->start_time->format('H:i') }} · {{ $st->start_time->format('d/m/Y') }}</strong></div>
                    <div class="col-6"><div class="text-secondary">Ghế</div><strong class="text-cine">{{ $booking->seats->map(fn ($s) => $s->seat->label)->join(', ') }}</strong></div>
                    <div class="col-6"><div class="text-secondary">Tổng tiền</div><strong class="fs-5">@money($booking->total_amount)</strong></div>
                    <div class="col-6"><div class="text-secondary">Đặt lúc</div><strong>{{ $booking->created_at->format('H:i d/m/Y') }}</strong></div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                @if ($isValid)
                    <div id="qrcode" class="d-inline-block"></div>
                    <div class="small text-secondary mt-2">Đưa mã này cho nhân viên soát vé</div>
                @else
                    <i class="bi bi-qr-code fs-1 text-secondary"></i>
                    <div class="small text-secondary">Mã QR hiển thị sau khi thanh toán</div>
                @endif
            </div>
        </div>
    </div>

    @if ($isPending)
        <div class="card mb-3">
            <div class="card-body">
                <h5>Thanh toán</h5>
                <p class="text-warning">Ghế được giữ đến <strong>{{ $booking->expires_at->format('H:i:s') }}</strong>
                    (còn <strong id="countdown" data-expires="{{ $booking->expires_at->timestamp }}">--:--</strong>).</p>
                <form method="POST" action="{{ route('bookings.pay', $booking) }}">
                    @csrf
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="method" id="mMock" value="mock" checked>
                        <label class="form-check-label" for="mMock"><i class="bi bi-credit-card"></i> Thanh toán online (giả lập)</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="method" id="mCash" value="cash">
                        <label class="form-check-label" for="mCash"><i class="bi bi-cash"></i> Thanh toán tại quầy</label>
                    </div>
                    <button class="btn btn-cine"><i class="bi bi-check2-circle"></i> Xác nhận thanh toán @money($booking->total_amount)</button>
                </form>
            </div>
        </div>
    @endif

    <div class="d-flex gap-2">
        <a href="{{ route('bookings.history') }}" class="btn btn-outline-secondary"><i class="bi bi-list-ul"></i> Vé của tôi</a>
        @if ($canCancel)
            <form method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này?')">
                @csrf
                <button class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> Hủy đơn</button>
            </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if ($isValid)
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>new QRCode(document.getElementById('qrcode'), { text: @json($booking->code), width: 160, height: 160 });</script>
@endif
@if ($isPending)
<script>
(() => {
    const el = document.getElementById('countdown');
    const end = Number(el.dataset.expires) * 1000;
    const tick = () => {
        const left = Math.max(0, Math.floor((end - Date.now()) / 1000));
        el.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
        if (left === 0) { clearInterval(timer); setTimeout(() => location.reload(), 1500); }
    };
    const timer = setInterval(tick, 1000); tick();
})();
</script>
@endif
@endpush
