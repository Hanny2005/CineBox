<?php

namespace App\Models;

use App\Enums\MovieStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movie extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'description', 'director', 'cast', 'duration',
        'release_date', 'age_rating', 'language', 'poster_url', 'trailer_url',
        'status', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'status' => MovieStatus::class,
            'is_featured' => 'boolean',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }

    public function scopeNowShowing(Builder $q): void
    {
        $q->where('status', MovieStatus::NowShowing);
    }

    public function scopeComingSoon(Builder $q): void
    {
        $q->where('status', MovieStatus::ComingSoon);
    }

    /** Link nhúng YouTube từ link trailer (watch?v=, youtu.be/, embed/). */
    public function getTrailerEmbedUrlAttribute(): ?string
    {
        if (! $this->trailer_url) {
            return null;
        }
        if (preg_match('~(?:youtu\.be/|v=|embed/)([A-Za-z0-9_-]{11})~', $this->trailer_url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        return null;
    }

    public function getDurationTextAttribute(): string
    {
        return intdiv($this->duration, 60).'h'.str_pad((string) ($this->duration % 60), 2, '0', STR_PAD_LEFT);
    }

    /** Poster hiển thị: link admin nhập, hoặc ảnh có sẵn public/images/posters/{slug}.svg|jpg|png */
    public function getPosterAttribute(): ?string
    {
        if ($this->poster_url) {
            return $this->poster_url;
        }
        foreach (['svg', 'jpg', 'png', 'webp'] as $ext) {
            if ($this->slug && is_file(public_path("images/posters/{$this->slug}.{$ext}"))) {
                return "/images/posters/{$this->slug}.{$ext}";
            }
        }

        return null;
    }
}
