<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MovieStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $movies = Movie::with('categories')
            ->withCount('showtimes')
            ->when($request->filled('q'), fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($request->q).'%']))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.movies.index', ['movies' => $movies, 'statuses' => MovieStatus::cases()]);
    }

    public function create()
    {
        return $this->form(new Movie(['status' => MovieStatus::ComingSoon, 'age_rating' => 'P']));
    }

    public function store(Request $request)
    {
        $movie = Movie::create($this->validated($request));
        $movie->categories()->sync($request->input('categories', []));

        return redirect()->route('admin.movies.index')->with('success', 'Đã thêm phim "'.$movie->title.'".');
    }

    public function edit(Movie $movie)
    {
        return $this->form($movie->load('categories'));
    }

    public function update(Request $request, Movie $movie)
    {
        $movie->update($this->validated($request, $movie));
        $movie->categories()->sync($request->input('categories', []));

        return redirect()->route('admin.movies.index')->with('success', 'Đã cập nhật phim "'.$movie->title.'".');
    }

    public function destroy(Movie $movie)
    {
        if ($movie->showtimes()->upcoming()->exists()) {
            return back()->with('error', 'Phim còn suất chiếu sắp tới, hãy tắt các suất chiếu trước khi xóa.');
        }

        $movie->delete(); // xóa mềm, vé cũ vẫn tra cứu được

        return back()->with('success', 'Đã xóa phim.');
    }

    private function form(Movie $movie)
    {
        return view('admin.movies.form', [
            'movie' => $movie,
            'categories' => Category::orderBy('name')->get(),
            'statuses' => MovieStatus::cases(),
        ]);
    }

    private function validated(Request $request, ?Movie $movie = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'director' => ['nullable', 'string', 'max:255'],
            'cast' => ['nullable', 'string', 'max:255'],
            'duration' => ['required', 'integer', 'min:1', 'max:600'],
            'release_date' => ['nullable', 'date'],
            'age_rating' => ['required', Rule::in(['P', 'K', 'T13', 'T16', 'T18'])],
            'language' => ['nullable', 'string', 'max:50'],
            'poster_url' => ['nullable', 'url', 'max:255'],
            'poster_file' => ['nullable', 'image', 'max:2048'],
            'trailer_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::enum(MovieStatus::class)],
            'categories' => ['array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ]);

        if ($request->hasFile('poster_file')) {
            $data['poster_url'] = Storage::disk('public')->url($request->file('poster_file')->store('posters', 'public'));
        }

        $slug = Str::slug($data['title']);
        $exists = Movie::withTrashed()->where('slug', $slug)->when($movie, fn ($q) => $q->whereKeyNot($movie->id))->exists();
        $data['slug'] = $exists ? $slug.'-'.Str::lower(Str::random(4)) : $slug;
        $data['is_featured'] = $request->boolean('is_featured');
        unset($data['poster_file'], $data['categories']);

        return $data;
    }
}
