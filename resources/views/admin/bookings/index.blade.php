@extends('layouts.admin')
@section('title', 'Đơn đặt vé')

@section('content')
<form class="row g-2 mb-3">
    <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Mã vé hoặc email khách..."></div>
    <div class="col-md-3"><select name="status" class="form-select"><option value="">Tất cả trạng thái</option>
        @foreach ($statuses as $s)<option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>@endforeach</select></div>
    <div class="col-md-2"><button class="btn btn-outline-light w-100">Lọc</button></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Mã vé</th><th>Khách hàng</th><th>Phim / Suất</th><th>Số ghế</th><th class="text-end">Tổng tiền</th><th>Trạng thái</th><th>Ngày đặt</th></tr></thead>
    <tbody>
    @forelse ($bookings as $b)
        <tr>
            <td><a href="{{ route('admin.bookings.show', $b) }}"><code>{{ $b->code }}</code></a></td>
            <td>{{ $b->user->name }}<div class="small text-secondary">{{ $b->user->email }}</div></td>
            <td>{{ $b->showtime->movie->title }}<div class="small text-secondary">{{ $b->showtime->room->cinema->name }} · {{ $b->showtime->start_time->format('H:i d/m/Y') }}</div></td>
            <td>{{ $b->seats_count }}</td>
            <td class="text-end">@money($b->total_amount)</td>
            <td><span class="badge bg-{{ $b->status->color() }}">{{ $b->status->label() }}</span></td>
            <td class="small">{{ $b->created_at->format('H:i d/m/Y') }}</td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-secondary">Không có đơn nào.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $bookings->links() }}</div>
@endsection
