<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng trung gian n-n (đặt tên theo quy ước Laravel: số ít, theo thứ tự ABC)
        Schema::create('category_movie', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'movie_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_movie');
    }
};
