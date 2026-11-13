<?php

namespace App\Models;

use App\Enums\SeatType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    protected $fillable = ['room_id', 'row_label', 'number', 'type', 'is_active'];

    protected $appends = ['label'];

    protected function casts(): array
    {
        return [
            'type' => SeatType::class,
            'is_active' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->row_label . $this->number; // "A5"
    }
}
