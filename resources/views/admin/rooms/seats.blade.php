@extends('layouts.admin')
@section('title', 'Sơ đồ ghế · '.$room->cinema->name.' · '.$room->name)

@section('content')
<p class="text-secondary">Bấm vào ghế để đổi loại theo vòng: <strong>Thường → VIP → Đôi → Tắt (lối đi/ghế hỏng)</strong>. Bấm "Lưu sơ đồ" để áp dụng.</p>
<div class="legend d-flex flex-wrap gap-3 small mb-3">
    <span><span class="seat"></span> Thường</span><span><span class="seat vip"></span> VIP</span>
    <span><span class="seat couple"></span> Đôi</span><span class="seat-editor"><span class="seat off"></span> Tắt</span>
</div>
<form method="POST" action="{{ route('admin.rooms.seats.update', $room) }}">
    @csrf @method('PUT')
    <div class="card mb-3"><div class="card-body">
        <div class="text-center small text-secondary mb-1">MÀN HÌNH</div>
        <div class="screen"></div>
        <div class="seat-map seat-editor">
            @foreach ($rows as $label => $seats)
                <div class="seat-row">
                    <span class="row-label">{{ $label }}</span>
                    @foreach ($seats as $seat)
                        @php($state = $seat->is_active ? $seat->type->value : 'off')
                        <button type="button" class="seat {{ $state }}" data-state="{{ $state }}" title="{{ $seat->label }}">{{ $seat->number }}</button>
                        <input type="hidden" name="seats[{{ $seat->id }}]" value="{{ $state }}">
                    @endforeach
                </div>
            @endforeach
        </div>
    </div></div>
    <button class="btn btn-cine"><i class="bi bi-save"></i> Lưu sơ đồ</button>
    <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">Quay lại</a>
</form>
@endsection

@push('scripts')
<script>
const order = ['standard', 'vip', 'couple', 'off'];
document.querySelectorAll('.seat-editor .seat-map .seat').forEach(btn => {
    btn.addEventListener('click', () => {
        const next = order[(order.indexOf(btn.dataset.state) + 1) % order.length];
        btn.classList.remove(btn.dataset.state);
        btn.classList.add(next);
        btn.dataset.state = next;
        btn.nextElementSibling.value = next;
    });
});
</script>
@endpush
