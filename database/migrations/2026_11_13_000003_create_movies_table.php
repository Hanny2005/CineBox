<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('director')->nullable();
            $table->string('cast')->nullable();
            $table->unsignedSmallInteger('duration');          // phút
            $table->date('release_date')->nullable();
            $table->string('age_rating', 10)->default('P');    // P, K, T13, T16, T18
            $table->string('language', 50)->nullable();
            $table->string('poster_url')->nullable();
            $table->string('trailer_url')->nullable();
            $table->string('status', 20)->default('coming_soon')->index(); // coming_soon | now_showing | ended
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes(); // xóa mềm để không mất lịch sử vé đã bán
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
