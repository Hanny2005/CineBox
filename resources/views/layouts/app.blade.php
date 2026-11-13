<!DOCTYPE html>
<html lang="vi" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Đặt vé xem phim') · {{ config('app.name', 'CineBox') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}"><i class="bi bi-film text-cine"></i> Cine<span>Box</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('movies.index') && request('status', 'now_showing') === 'now_showing' ? 'active' : '' }}" href="{{ route('movies.index') }}">Phim đang chiếu</a></li>
                <li class="nav-item"><a class="nav-link {{ request('status') === 'coming_soon' ? 'active' : '' }}" href="{{ route('movies.index', ['status' => 'coming_soon']) }}">Phim sắp chiếu</a></li>
            </ul>
            <form class="d-flex me-lg-3 my-2 my-lg-0" action="{{ route('movies.index') }}">
                <input type="hidden" name="status" value="all">
                <input class="form-control form-control-sm" type="search" name="q" value="{{ request('q') }}" placeholder="Tìm phim...">
            </form>
            <ul class="navbar-nav">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> {{ auth()->user()->name }}</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('bookings.history') }}"><i class="bi bi-ticket-perforated me-2"></i>Vé của tôi</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear me-2"></i>Tài khoản</a></li>
                            @if (auth()->user()->isStaff())
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('staff.tickets.index') }}"><i class="bi bi-qr-code-scan me-2"></i>Soát vé</a></li>
                            @endif
                            @if (auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Trang quản trị</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Đăng nhập</a></li>
                    <li class="nav-item"><a class="btn btn-cine btn-sm ms-lg-2 mt-1" href="{{ route('register') }}">Đăng ký</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        @include('partials.flash')
    </div>
    @yield('content')
</main>

<footer class="border-top py-4 mt-5 text-secondary small">
    <div class="container d-flex flex-wrap justify-content-between gap-2">
        <span>© {{ date('Y') }} {{ config('app.name', 'CineBox') }} — Đồ án website đặt vé xem phim (Laravel + Supabase)</span>
        <span>Hotline: 1900 0000 · Giờ mở cửa 8:00 – 24:00</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
