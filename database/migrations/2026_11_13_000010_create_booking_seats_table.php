<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('showtime_id')->constrained()->restrictOnDelete();
            $table->foreignId('seat_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('price'); // chốt giá tại thời điểm đặt

            /*
             * Chống đặt trùng ghế ở tầng database:
             * - Khi ghế đang được giữ/đã bán: active_showtime_id = showtime_id
             * - Khi đơn bị hủy/hết hạn:       active_showtime_id = NULL
             * UNIQUE(active_showtime_id, seat_id) cho phép nhiều dòng NULL,
             * nên lịch sử đơn hủy vẫn được giữ mà ghế lại được mở bán.
             * Nếu 2 request cùng chèn 1 ghế, request thứ 2 bị lỗi duplicate key.
             */
            $table->unsignedBigInteger('active_showtime_id')->nullable();
            $table->timestamps();

            $table->unique(['active_showtime_id', 'seat_id'], 'booking_seats_active_unique');
            $table->index(['showtime_id', 'seat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_seats');
    }
};
