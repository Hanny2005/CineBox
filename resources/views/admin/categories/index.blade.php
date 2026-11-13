@extends('layouts.admin')
@section('title', 'Thể loại phim')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="card"><div class="card-body">
            @csrf
            <h5>Thêm thể loại</h5>
            <input name="name" class="form-control mb-2" placeholder="VD: Hành động" required>
            <button class="btn btn-cine w-100">Thêm</button>
        </div></form>
    </div>
    <div class="col-md-8">
        <div class="card"><table class="table align-middle mb-0">
            <thead><tr><th>Tên thể loại</th><th>Số phim</th><th></th></tr></thead>
            <tbody>
            @forelse ($categories as $c)
                <tr>
                    <td>
                        <form method="POST" action="{{ route('admin.categories.update', $c) }}" class="d-flex gap-2">
                            @csrf @method('PUT')
                            <input name="name" value="{{ $c->name }}" class="form-control form-control-sm">
                            <button class="btn btn-sm btn-outline-light" title="Lưu"><i class="bi bi-check-lg"></i></button>
                        </form>
                    </td>
                    <td>{{ $c->movies_count }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Xóa thể loại {{ $c->name }}?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-secondary">Chưa có thể loại.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
</div>
@endsection
