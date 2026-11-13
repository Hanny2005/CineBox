<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Cinema extends Model
{
    protected $fillable = ['name', 'address', 'city', 'phone', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function showtimes(): HasManyThrough
    {
        return $this->hasManyThrough(Showtime::class, Room::class);
    }
}
