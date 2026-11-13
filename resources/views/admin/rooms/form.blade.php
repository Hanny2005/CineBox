@extends('layouts.admin')
@section('title', $room->exists ? 'Sửa phòng chiếu' : 'Thêm phòng chiếu')

@section('content')
<form method="POST" action="{{ $room->exists ? route('admin.rooms.update', $room) : route('admin.rooms.store') }}" class="card" style="max-width: 640px"><div class="card-body row g-3">
    @csrf
    @if ($room->exists) @method('PUT') @endif
    <div class="col-12"><label class="form-label">Rạp *</label>
        <select name="cinema_id" class="form-select" required>
            @foreach ($cinemas as $c)<option value="{{ $c->id }}" @selected(old('cinema_id', $room->cinema_id) == $c->id)>{{ $c->name }}</option>@endforeach
        </select></div>
    <div class="col-md-8"><label class="form-label">Tên phòng *</label><input name="name" value="{{ old('name', $room->name) }}" class="form-control" placeholder="Phòng 1" required></div>
    <div class="col-md-4"><label class="form-label">Định dạng</label>
        <select name="format" class="form-select">@foreach (['2D', '3D', 'IMAX'] as $f)<option @selected(old('format', $room->format) === $f)>{{ $f }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Số hàng ghế (A → Z) *</label>
        <input type="number" name="total_rows" min="1" max="26" value="{{ old('total_rows', $room->total_rows) }}" class="form-control" @readonly($locked) required></div>
    <div class="col-md-6"><label class="form-label">Số ghế mỗi hàng *</label>
        <input type="number" name="seats_per_row" min="1" max="30" value="{{ old('seats_per_row', $room->seats_per_row) }}" class="form-control" @readonly($locked) required></div>
    @if ($locked)
        <div class="col-12 small text-warning">Phòng đã có lịch chiếu nên không đổi được bố cục. Hãy dùng "Sơ đồ ghế" để chỉnh từng ghế.</div>
    @elseif ($room->exists)
        <div class="col-12 small text-warning">Nếu đổi số hàng hoặc số ghế, toàn bộ sơ đồ ghế sẽ được tạo lại.</div>
    @else
        <div class="col-12 small text-secondary">Hệ thống tự sinh ghế: hàng cuối là ghế đôi, các hàng giữa phía sau là VIP. Bạn có thể chỉnh lại sau.</div>
    @endif
    <div class="col-12 form-check form-switch ms-2">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" @checked(old('is_active', $room->is_active))>
        <label class="form-check-label" for="active">Đang hoạt động</label>
    </div>
    <div class="col-12 d-flex gap-2">
        <button class="btn btn-cine"><i class="bi bi-save"></i> Lưu</button>
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div></form>
@endsection
