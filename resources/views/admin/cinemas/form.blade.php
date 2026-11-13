@extends('layouts.admin')
@section('title', $cinema->exists ? 'Sửa rạp' : 'Thêm rạp')

@section('content')
<form method="POST" action="{{ $cinema->exists ? route('admin.cinemas.update', $cinema) : route('admin.cinemas.store') }}" class="card" style="max-width: 640px"><div class="card-body row g-3">
    @csrf
    @if ($cinema->exists) @method('PUT') @endif
    <div class="col-12"><label class="form-label">Tên rạp *</label><input name="name" value="{{ old('name', $cinema->name) }}" class="form-control" required></div>
    <div class="col-12"><label class="form-label">Địa chỉ *</label><input name="address" value="{{ old('address', $cinema->address) }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Thành phố *</label><input name="city" value="{{ old('city', $cinema->city) }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Điện thoại</label><input name="phone" value="{{ old('phone', $cinema->phone) }}" class="form-control"></div>
    <div class="col-12 form-check form-switch ms-2">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active" @checked(old('is_active', $cinema->is_active))>
        <label class="form-check-label" for="active">Đang hoạt động</label>
    </div>
    <div class="col-12 d-flex gap-2">
        <button class="btn btn-cine"><i class="bi bi-save"></i> Lưu</button>
        <a href="{{ route('admin.cinemas.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div></form>
@endsection
