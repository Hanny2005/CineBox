@extends('layouts.app')
@section('title', 'Tài khoản')

@section('content')
<div class="container" style="max-width: 760px">
    <h3 class="mb-4">Tài khoản của tôi</h3>
    <div class="row g-4">
        <div class="col-md-6">
            <form method="POST" action="{{ route('profile.update') }}" class="card"><div class="card-body">
                @csrf @method('PUT')
                <h5 class="mb-3">Thông tin cá nhân</h5>
                <div class="mb-3"><label class="form-label">Họ tên</label><input name="name" value="{{ old('name', $user->name) }}" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Số điện thoại</label><input name="phone" value="{{ old('phone', $user->phone) }}" class="form-control"></div>
                <p class="small text-secondary">Vai trò: {{ $user->role->label() }}</p>
                <button class="btn btn-cine">Lưu thay đổi</button>
            </div></form>
        </div>
        <div class="col-md-6">
            <form method="POST" action="{{ route('profile.password') }}" class="card"><div class="card-body">
                @csrf @method('PUT')
                <h5 class="mb-3">Đổi mật khẩu</h5>
                <div class="mb-3"><label class="form-label">Mật khẩu hiện tại</label><input type="password" name="current_password" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Mật khẩu mới</label><input type="password" name="password" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Nhập lại mật khẩu mới</label><input type="password" name="password_confirmation" class="form-control" required></div>
                <button class="btn btn-outline-light">Đổi mật khẩu</button>
            </div></form>
        </div>
    </div>
</div>
@endsection
