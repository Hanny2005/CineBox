<!DOCTYPE html>
<html lang="vi" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Quản trị') · {{ config('app.name', 'CineBox') }} Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
@php
    $menu = [
        ['admin.dashboard', 'speedometer2', 'Tổng quan', 'admin.dashboard'],
        ['admin.movies.index', 'film', 'Phim', 'admin.movies.*'],
        ['admin.categories.index', 'tags', 'Thể loại', 'admin.categories.*'],
        ['admin.cinemas.index', 'building', 'Rạp chiếu', 'admin.cinemas.*'],
        ['admin.rooms.index', 'grid-3x3', 'Phòng & ghế', 'admin.rooms.*'],
        ['admin.showtimes.index', 'calendar3', 'Lịch chiếu', 'admin.showtimes.*'],
        ['admin.bookings.index', 'receipt', 'Đơn đặt vé', 'admin.bookings.*'],
        ['admin.users.index', 'people', 'Tài khoản', 'admin.users.*'],
    ];
@endphp
<div class="d-flex">
    <aside class="admin-sidebar p-3 d-none d-md-block flex-shrink-0">
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand d-block mb-4 fs-5"><i class="bi bi-film text-cine"></i> Cine<span>Box</span> <small class="text-secondary fs-6">admin</small></a>
        <nav class="nav flex-column gap-1">
            @foreach ($menu as [$route, $icon, $label, $pattern])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}"><i class="bi bi-{{ $icon }} me-2"></i>{{ $label }}</a>
            @endforeach
            <hr>
            <a href="{{ route('staff.tickets.index') }}" class="nav-link"><i class="bi bi-qr-code-scan me-2"></i>Soát vé</a>
            <a href="{{ route('home') }}" class="nav-link"><i class="bi bi-house me-2"></i>Về trang chủ</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="nav-link text-danger border-0 bg-transparent w-100 text-start"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button>
            </form>
        </nav>
    </aside>

    <div class="flex-grow-1 min-vw-0" style="min-width: 0">
        <nav class="navbar bg-body-tertiary border-bottom d-md-none px-3">
            <a href="{{ route('admin.dashboard') }}" class="navbar-brand">Cine<span>Box</span> admin</a>
            <select class="form-select form-select-sm w-auto" onchange="location = this.value">
                @foreach ($menu as [$route, $icon, $label, $pattern])
                    <option value="{{ route($route) }}" @selected(request()->routeIs($pattern))>{{ $label }}</option>
                @endforeach
            </select>
        </nav>
        <main class="p-3 p-lg-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <h3 class="mb-0">@yield('title')</h3>
                <div>@yield('actions')</div>
            </div>
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
