@extends('layouts.admin')
@section('title', $movie->exists ? 'Sửa phim' : 'Thêm phim')

@section('content')
@php($selected = old('categories', $movie->categories->pluck('id')->all()))
<form method="POST" enctype="multipart/form-data" action="{{ $movie->exists ? route('admin.movies.update', $movie) : route('admin.movies.store') }}">
    @csrf
    @if ($movie->exists) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card"><div class="card-body row g-3">
                <div class="col-12"><label class="form-label">Tên phim *</label><input name="title" value="{{ old('title', $movie->title) }}" class="form-control" required></div>
                <div class="col-12"><label class="form-label">Nội dung</label><textarea name="description" rows="5" class="form-control">{{ old('description', $movie->description) }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Đạo diễn</label><input name="director" value="{{ old('director', $movie->director) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Diễn viên</label><input name="cast" value="{{ old('cast', $movie->cast) }}" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Thời lượng (phút) *</label><input type="number" name="duration" min="1" value="{{ old('duration', $movie->duration) }}" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Khởi chiếu</label><input type="date" name="release_date" value="{{ old('release_date', $movie->release_date?->toDateString()) }}" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Phân loại *</label>
                    <select name="age_rating" class="form-select">
                        @foreach (['P', 'K', 'T13', 'T16', 'T18'] as $r)<option @selected(old('age_rating', $movie->age_rating) === $r)>{{ $r }}</option>@endforeach
                    </select></div>
                <div class="col-md-3"><label class="form-label">Ngôn ngữ</label><input name="language" value="{{ old('language', $movie->language) }}" class="form-control"></div>
                <div class="col-12"><label class="form-label">Link trailer (YouTube)</label><input type="url" name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}" class="form-control" placeholder="https://www.youtube.com/watch?v=..."></div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3"><div class="card-body">
                <label class="form-label">Trạng thái *</label>
                <select name="status" class="form-select mb-3">
                    @foreach ($statuses as $s)<option value="{{ $s->value }}" @selected(old('status', $movie->status?->value) === $s->value)>{{ $s->label() }}</option>@endforeach
                </select>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featured" @checked(old('is_featured', $movie->is_featured))>
                    <label class="form-check-label" for="featured">Phim nổi bật (hiện ở banner trang chủ)</label>
                </div>
                <label class="form-label">Thể loại</label>
                <div class="border rounded p-2" style="max-height: 180px; overflow:auto">
                    @foreach ($categories as $c)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $c->id }}" id="cat{{ $c->id }}" @checked(in_array($c->id, $selected))>
                            <label class="form-check-label" for="cat{{ $c->id }}">{{ $c->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div></div>
            <div class="card"><div class="card-body">
                <label class="form-label">Poster</label>
                @if ($movie->poster_url)<div class="mb-2" style="max-width:140px">@include('partials.poster', ['movie' => $movie])</div>@endif
                <input type="file" name="poster_file" accept="image/*" class="form-control mb-2">
                <div class="small text-secondary mb-1">hoặc dán link ảnh:</div>
                <input type="url" name="poster_url" value="{{ old('poster_url', $movie->poster_url) }}" class="form-control" placeholder="https://...">
            </div></div>
        </div>
    </div>
    <div class="mt-4 d-flex gap-2">
        <button class="btn btn-cine"><i class="bi bi-save"></i> Lưu</button>
        <a href="{{ route('admin.movies.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</form>
@endsection
