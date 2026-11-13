@extends('layouts.app')
@section('title', 'Chọn ghế · '.$showtime->movie->title)

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <a href="{{ route('movies.show', $showtime->movie) }}#lich-chieu" class="link-secondary small"><i class="bi bi-arrow-left"></i> Đổi suất chiếu</a>
            <h3 class="mb-1">{{ $showtime->movie->title }}</h3>
            <div class="text-secondary">
                {{ $showtime->room->cinema->name }} · {{ $showtime->room->name }} ({{ $showtime->room->format }}) ·
                <strong class="text-light">{{ $showtime->start_time->format('H:i') }}</strong> ngày {{ $showtime->start_time->format('d/m/Y') }}
            </div>
        </div>
        <div class="legend d-flex flex-wrap gap-3 small align-items-center">
            <span><span class="seat"></span> Thường</span>
            <span><span class="seat vip"></span> VIP (+@money(\App\Enums\SeatType::Vip->surcharge()))</span>
            <span><span class="seat couple"></span> Đôi (+@money(\App\Enums\SeatType::Couple->surcharge()))</span>
            <span><span class="seat selected"></span> Đang chọn</span>
            <span><span class="seat taken"></span> Đã đặt</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card"><div class="card-body">
                <div class="text-center small text-secondary mb-1">MÀN HÌNH</div>
                <div class="screen"></div>
                <div class="seat-map">
                    @foreach ($rows as $label => $seats)
                        <div class="seat-row">
                            <span class="row-label">{{ $label }}</span>
                            @foreach ($seats as $seat)
                                @php($isTaken = in_array($seat->id, $taken))
                                <button type="button"
                                    class="seat {{ $seat->type->value }} {{ $isTaken ? 'taken' : '' }} {{ $seat->is_active ? '' : 'off' }}"
                                    data-id="{{ $seat->id }}" data-label="{{ $seat->label }}"
                                    data-price="{{ $showtime->base_price + $seat->type->surcharge() }}"
                                    @disabled($isTaken || ! $seat->is_active)
                                    title="{{ $seat->label }} · {{ $seat->type->label() }}">{{ $seat->number }}</button>
                            @endforeach
                            <span class="row-label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div></div>
        </div>

        <div class="col-lg-4">
            <form method="POST" action="{{ route('bookings.store', $showtime) }}" id="bookingForm" class="card position-sticky" style="top: 80px">
                @csrf
                <div class="card-body">
                    <h5>Thông tin đặt vé</h5>
                    <p class="small text-secondary mb-2">Giá vé thường: @money($showtime->base_price) · Tối đa {{ $maxSeats }} ghế/đơn</p>
                    <div class="mb-2">Ghế đã chọn: <strong id="selectedLabels" class="text-cine">Chưa chọn</strong></div>
                    <div class="fs-4 mb-3">Tổng: <strong id="total">0 đ</strong></div>
                    <div id="seatInputs"></div>
                    <button class="btn btn-cine w-100" id="submitBtn" disabled><i class="bi bi-lock"></i> Giữ ghế & thanh toán</button>
                    <p class="small text-secondary mt-2 mb-0">Ghế sẽ được giữ trong 10 phút để bạn thanh toán.</p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const max = {{ $maxSeats }};
    const selected = new Map();
    const fmt = n => n.toLocaleString('vi-VN') + ' đ';
    const labels = document.getElementById('selectedLabels');
    const total = document.getElementById('total');
    const inputs = document.getElementById('seatInputs');
    const btn = document.getElementById('submitBtn');

    function render() {
        labels.textContent = selected.size ? [...selected.values()].map(s => s.label).join(', ') : 'Chưa chọn';
        total.textContent = fmt([...selected.values()].reduce((sum, s) => sum + s.price, 0));
        inputs.innerHTML = [...selected.keys()].map(id => `<input type="hidden" name="seats[]" value="${id}">`).join('');
        btn.disabled = selected.size === 0;
    }

    document.querySelectorAll('.seat-map .seat:not([disabled])').forEach(el => {
        el.addEventListener('click', () => {
            const id = el.dataset.id;
            if (selected.has(id)) {
                selected.delete(id);
                el.classList.remove('selected');
            } else {
                if (selected.size >= max) { alert('Mỗi đơn chỉ được chọn tối đa ' + max + ' ghế.'); return; }
                selected.set(id, { label: el.dataset.label, price: Number(el.dataset.price) });
                el.classList.add('selected');
            }
            render();
        });
    });

    document.getElementById('bookingForm').addEventListener('submit', () => { btn.disabled = true; btn.textContent = 'Đang giữ ghế...'; });
})();
</script>
@endpush
