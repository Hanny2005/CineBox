@extends('layouts.auth')
@section('title', 'Đăng ký')

@section('content')
<h1 class="auth-title">Tạo tài khoản</h1>
<p class="auth-subtitle">Đặt vé nhanh, giữ ghế đẹp, xem lại vé mọi lúc.</p>

@if ($errors->any())
    <div class="alert alert-danger auth-alert py-2"><ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('register') }}" novalidate>
    @csrf
    <div class="mb-3">
        <label class="auth-label" for="name">Họ tên</label>
        <input id="name" name="name" value="{{ old('name') }}" class="form-control auth-input @error('name') is-invalid @enderror" placeholder="Nguyễn Văn A" autocomplete="name" required autofocus>
    </div>
    <div class="mb-3">
        <label class="auth-label" for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control auth-input @error('email') is-invalid @enderror" placeholder="ban@email.com" autocomplete="email" required>
    </div>
    <div class="mb-3">
        <label class="auth-label" for="phone">Số điện thoại <span class="text-secondary fw-normal">(không bắt buộc)</span></label>
        <input id="phone" name="phone" value="{{ old('phone') }}" class="form-control auth-input" placeholder="09xx xxx xxx" autocomplete="tel">
    </div>
    <div class="mb-3">
        <label class="auth-label" for="password">Mật khẩu</label>
        <div class="auth-password">
            <input type="password" id="password" name="password" class="form-control auth-input @error('password') is-invalid @enderror" placeholder="Tối thiểu 8 ký tự" autocomplete="new-password" required>
            <button type="button" class="auth-eye" data-toggle-password="password" aria-label="Hiện mật khẩu"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <div class="mb-4">
        <label class="auth-label" for="password_confirmation">Nhập lại mật khẩu</label>
        <div class="auth-password">
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control auth-input" placeholder="••••••••" autocomplete="new-password" required>
            <button type="button" class="auth-eye" data-toggle-password="password_confirmation" aria-label="Hiện mật khẩu"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <button class="auth-btn" type="submit"><i class="bi bi-person-plus"></i> Đăng ký</button>
</form>

<p class="auth-footer">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
@endsection
