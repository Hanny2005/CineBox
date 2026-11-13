@extends('layouts.admin')
@section('title', 'Tổng quan')

@section('content')
@php
    $cards = [
        ['Doanh thu tháng này', number_format($stats['revenue_month'], 0, ',', '.').' đ', 'cash-stack', 'success'],
        ['Tổng doanh thu', number_format($stats['revenue_total'], 0, ',', '.').' đ', 'graph-up-arrow', 'primary'],
        ['Vé đã bán', number_format($stats['tickets_sold']), 'ticket-perforated', 'danger'],
        ['Đơn hôm nay', $stats['bookings_today'], 'receipt', 'warning'],
        ['Phim đang chiếu', $stats['movies_showing'], 'film', 'info'],
        ['Khách hàng', $stats['customers'], 'people', 'secondary'],
    ];
    $maxValue = max(1, $chart->max('value'));
@endphp
<div class="row row-cols-2 row-cols-lg-3 row-cols-xxl-6 g-3 mb-4">
    @foreach ($cards as [$label, $value, $icon, $color])
        <div class="col"><div class="card stat-card h-100"><div class="card-body d-flex justify-content-between">
            <div><div class="small text-secondary">{{ $label }}</div><div class="fs-4 fw-bold">{{ $value }}</div></div>
            <i class="bi bi-{{ $icon }} text-{{ $color }}"></i>
        </div></div></div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card h-100"><div class="card-body">
            <h5 class="mb-3">Doanh thu 7 ngày gần nhất</h5>
            <div class="d-flex align-items-end gap-2" style="height: 220px">
                @foreach ($chart as $day)
                    <div class="flex-fill d-flex flex-column justify-content-end text-center h-100">
                        <div class="small text-secondary mb-1">{{ $day['value'] ? number_format($day['value'] / 1000).'k' : '' }}</div>
                        <div class="bar" style="height: {{ round($day['value'] / $maxValue * 170) }}px" title="@money($day['value'])"></div>
                        <div class="small mt-1">{{ $day['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100"><div class="card-body">
            <h5 class="mb-3">Top phim theo doanh thu</h5>
            <table class="table table-sm mb-0">
                <thead><tr><th>Phim</th><th class="text-end">Đơn</th><th class="text-end">Doanh thu</th></tr></thead>
                <tbody>
                @forelse ($topMovies as $m)
                    <tr><td>{{ $m->title }}</td><td class="text-end">{{ $m->orders }}</td><td class="text-end">@money($m->revenue)</td></tr>
                @empty
                    <tr><td colspan="3" class="text-secondary">Chưa có dữ liệu.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
    <div class="col-12">
        <div class="card"><div class="card-body">
            <div class="d-flex justify-content-between"><h5 class="mb-3">Đơn đặt vé mới nhất</h5><a href="{{ route('admin.bookings.index') }}">Xem tất cả</a></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Mã</th><th>Khách</th><th>Phim</th><th>Suất</th><th class="text-end">Tiền</th><th>Trạng thái</th></tr></thead>
                    <tbody>
                    @forelse ($latest as $b)
                        <tr>
                            <td><a href="{{ route('admin.bookings.show', $b) }}"><code>{{ $b->code }}</code></a></td>
                            <td>{{ $b->user->name }}</td>
                            <td>{{ $b->showtime->movie->title }}</td>
                            <td>{{ $b->showtime->start_time->format('H:i d/m') }}</td>
                            <td class="text-end">@money($b->total_amount)</td>
                            <td><span class="badge bg-{{ $b->status->color() }}">{{ $b->status->label() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-secondary">Chưa có đơn nào.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
@endsection
