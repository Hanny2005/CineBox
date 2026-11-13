@extends('layouts.auth')
@section('title', 'Đăng nhập')

@section('content')
<h1 class="auth-title">Đăng nhập</h1>
<p class="auth-subtitle">Chào mừng bạn quay lại rạp phim.</p>

@if ($errors->any())
    <div class="alert alert-danger auth-alert py-2">{{ $errors->first() }}</div>
@endif
@if (session('success'))
    <div class="alert alert-success auth-alert py-2">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
    @csrf
    <div class="mb-4">
        <label class="auth-label" for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control auth-input @error('email') is-invalid @enderror"
               placeholder="ban@email.com" autocomplete="email" required autofocus>
    </div>
    <div class="mb-4">
        <label class="auth-label" for="password">Mật khẩu</label>
        <div class="auth-password">
            <input type="password" id="password" name="password" class="form-control auth-input" placeholder="••••••••" autocomplete="current-password" required>
            <button type="button" class="auth-eye" data-toggle-password="password" aria-label="Hiện mật khẩu"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label text-secondary" for="remember">Ghi nhớ đăng nhập</label>
        </div>
    </div>
    <button class="auth-btn" type="submit"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</button>
</form>

@if (app()->isLocal())
    @php
        $demos = [
            ['admin', 'shield-check', 'Quản trị viên', 'admin@cinema.test', 'Toàn quyền: phim, rạp, lịch chiếu, đơn vé, doanh thu'],
            ['staff', 'qr-code-scan', 'Nhân viên', 'staff@cinema.test', 'Soát vé, xác nhận khách vào rạp'],
            ['user', 'person', 'Khách hàng', 'user@cinema.test', 'Xem phim, chọn ghế, đặt vé, xem lịch sử'],
        ];
    @endphp
    <div class="auth-divider">hoặc</div>
    <button class="auth-outline" type="button" data-bs-toggle="collapse" data-bs-target="#demoAccounts" aria-expanded="false" aria-controls="demoAccounts">
        Dùng tài khoản mẫu <i class="bi bi-chevron-down"></i>
    </button>
    <div class="collapse" id="demoAccounts">
        @foreach ($demos as [$key, $icon, $role, $email, $desc])
            <button type="button" class="demo-card" data-email="{{ $email }}" data-password="password" title="Bấm để điền và đăng nhập">
                <span class="demo-icon {{ $key }}"><i class="bi bi-{{ $icon }}"></i></span>
                <span class="demo-body">
                    <span class="demo-role d-block">{{ $role }}</span>
                    <span class="demo-email d-block">{{ $email }}</span>
                    <span class="demo-desc d-block">{{ $desc }}</span>
                </span>
                <span class="demo-pass">password</span>
            </button>
        @endforeach
    </div>
@endif

<p class="auth-footer">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a></p>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.demo-card').forEach(card => {
    card.addEventListener('click', () => {
        document.getElementById('email').value = card.dataset.email;
        document.getElementById('password').value = card.dataset.password;
        document.getElementById('loginForm').requestSubmit();
    });
});
</script>
@endpush
