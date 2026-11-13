<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Supabase công khai schema "public" qua Data API (REST/GraphQL) bằng anon key.
 * Bật Row Level Security mà không tạo policy => Data API không đọc/ghi được gì,
 * còn Laravel kết nối bằng user "postgres" (chủ sở hữu bảng) nên vẫn hoạt động bình thường.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tables = DB::select("select tablename from pg_tables where schemaname = 'public'");
        foreach ($tables as $t) {
            DB::statement(sprintf('alter table public.%s enable row level security', '"'.$t->tablename.'"'));
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tables = DB::select("select tablename from pg_tables where schemaname = 'public'");
        foreach ($tables as $t) {
            DB::statement(sprintf('alter table public.%s disable row level security', '"'.$t->tablename.'"'));
        }
    }
};
