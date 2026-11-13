<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('showtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->restrictOnDelete();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time'); // = start_time + duration + thời gian dọn phòng
            $table->unsignedInteger('base_price'); // VNĐ, giá ghế thường
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Truy vấn hay dùng: lịch chiếu theo phim/ngày và kiểm tra trùng giờ trong 1 phòng
            $table->index(['movie_id', 'start_time']);
            $table->index(['room_id', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }
};
