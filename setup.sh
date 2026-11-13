#!/usr/bin/env bash
# CineBox - cài đặt và chạy (macOS/Linux). Cần PHP 8.2+ (pdo_sqlite) và Composer.
set -e
cd "$(dirname "$0")"
command -v php >/dev/null || { echo "Chưa có PHP 8.2+"; exit 1; }
command -v composer >/dev/null || { echo "Chưa có Composer: https://getcomposer.org"; exit 1; }
[ -d vendor ] || composer install --no-interaction --prefer-dist
[ -f database/database.sqlite ] && [ -s database/database.sqlite ] || composer run setup
echo "Tài khoản thử (mật khẩu: password): admin@cinema.test | staff@cinema.test | user@cinema.test"
php artisan serve
