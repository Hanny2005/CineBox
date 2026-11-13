@extends('layouts.admin')
@section('title', $showtime->exists ? 'Sửa suất chiếu' : 'Thêm suất chiếu')

@section('content')
<form method="POST" action="{{ $showtime->exists ? route('admin.showtimes.update', $showtime) : route('admin.showtimes.store') }}" class="card" style="max-width: 640px"><div class="card-body row g-3">
    @csrf
    @if ($showtime->exists) @method('PUT') @endif
    <div class="col-12"><label class="form-label">Phim *</label>
        <select name="movie_id" class="form-select" required>
            <option value="">-- Chọn phim --</option>
            @foreach ($movies as $m)<option value="{{ $m->id }}" @selected(old('movie_id', $showtime->movie_id) == $m->id)>{{ $m->title }} ({{ $m->duration }} phút)</option>@endforeach
        </select></div>
    <div class="col-12"><label class="form-label">Phòng chiếu *</label>
        <select name="room_id" class="form-select" required>
            @foreach ($rooms as $r)<option value="{{ $r->id }}" @selected(old('room_id', $showtime->room_id) == $r->id)>{{ $r->cinema->name }} · {{ $r->name }} ({{ $r->format }})</option>@endforeach
        </select></div>
    <div class="col-md-7"><label class="form-label">Giờ bắt đầu *</label>
        <input type="datetime-local" name="start_time" value="{{ old('start_time', $showtime->start_time?->format('Y-m-d\TH:i')) }}" class="form-control @error('start_time') is-invalid @enderror" required>
        <div class="form-text">Giờ kết thúc tự tính = giờ bắt đầu + thời lượng phim + 15 phút dọn phòng.</div></div>
    <div class="col-md-5"><label class="form-label">Giá vé thường (VNĐ) *</label>
        <input type="number" name="base_price" step="1000" min="0" value="{{ old('base_price', $showtime->base_price) }}" class="form-control" required>
        <div class="form-text">VIP +{{ number_format(\App\Enums\SeatType::Vip->surcharge()) }}, ghế đôi +{{ number_format(\App\Enums\SeatType::Couple->surcharge()) }}</div></div>
    <div class="col-12 form-check form-switch ms-2">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" @checked(old('is_active', $showtime->is_active))>
        <label class="form-check-label" for="active">Mở bán</label>
    </div>
    <div class="col-12 d-flex gap-2">
        <button class="btn btn-cine"><i class="bi bi-save"></i> Lưu</button>
        <a href="{{ route('admin.showtimes.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div></form>
@endsection
