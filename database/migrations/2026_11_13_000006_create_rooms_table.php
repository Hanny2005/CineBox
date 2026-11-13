<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cinema_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);                 // "Phòng 1", "IMAX"...
            $table->string('format', 20)->default('2D'); // 2D | 3D | IMAX
            $table->unsignedTinyInteger('total_rows');   // dùng để vẽ sơ đồ ghế
            $table->unsignedTinyInteger('seats_per_row');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['cinema_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
