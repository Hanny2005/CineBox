#!/bin/sh
# Khởi động CineBox trong container
set -e
cd /app

[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    # SQLite: tạo lại dữ liệu mẫu mỗi lần khởi động (bản demo)
    touch database/database.sqlite
    php artisan migrate:fresh --seed --force
else
    # PostgreSQL/Supabase: giữ dữ liệu, chỉ seed khi DB còn trống
    php artisan migrate --force
    php artisan db:seed --force 2>/dev/null || echo "Bỏ qua seed (dữ liệu đã có)"
fi

php artisan storage:link 2>/dev/null || true
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
