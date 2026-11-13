---
name: cinebox
description: Làm việc với dự án CineBox, website đặt vé xem phim viết bằng Laravel 12 + Blade + Bootstrap 5, dùng Supabase PostgreSQL hoặc SQLite. Dùng skill này khi thêm/sửa tính năng, sửa lỗi, đổi database, chạy migrate/seed hoặc triển khai dự án.
---

# CineBox: hướng dẫn cho AI agent

## Tổng quan
Website đặt vé xem phim có 3 vai trò: **customer**, **staff** (soát vé), **admin** (quản trị).
Stack: Laravel 12, PHP 8.2+, Blade + Bootstrap 5.3 (CDN, **không dùng Vite/npm**), Eloquent ORM, PHP Enum.
Database: SQLite mặc định; Supabase PostgreSQL qua `DB_CONNECTION=pgsql` + `DB_URL` (Session pooler, cổng 5432).

## Lệnh thường dùng
```bash
composer install
composer run setup            # .env + key + migrate:fresh --seed + storage:link (XÓA dữ liệu cũ)
php artisan serve             # http://localhost:8000 (Windows bị chặn cổng thì dùng --port=8080)
php artisan migrate           # chạy migration mới, giữ dữ liệu
php artisan bookings:expire   # nhả ghế của các đơn pending quá hạn
php artisan route:list
```
Tài khoản mẫu (mật khẩu `password`): `admin@cinema.test`, `staff@cinema.test`, `user@cinema.test`.

## Kiến trúc
| Thành phần | Vị trí | Ghi chú |
|---|---|---|
| Enum | `app/Enums` | `UserRole`, `SeatType` (phụ thu VIP/đôi), `MovieStatus`, `BookingStatus` (`holdsSeats()`), `PaymentStatus`; có `label()`/`color()` để hiển thị |
| Model | `app/Models` | `Movie` có accessor `poster` (lấy `poster_url`, nếu trống thì lấy `public/images/posters/{slug}.svg`) |
| Nghiệp vụ đặt vé | `app/Services/BookingService.php` | `create`, `pay`, `cancel`, `checkIn`, `expireStale`, `takenSeatIds`. **Mọi logic đặt vé đi qua service này** |
| Phân quyền | `app/Http/Middleware/EnsureRole.php` | Alias `role` trong `bootstrap/app.php`; dùng `role:admin` hoặc `role:staff,admin` |
| Controller | `app/Http/Controllers` | Khách hàng ở gốc, `Admin/*`, `Staff/TicketController`, `Auth/AuthController` |
| Route | `routes/web.php` | URL tiếng Việt: `/phim`, `/suat-chieu/{id}/chon-ghe`, `/ve/{code}`, `/admin/...`, `/nhan-vien/soat-ve` |
| View | `resources/views` | Layout: `layouts/app` (khách), `layouts/admin`, `layouts/auth` (đăng nhập/đăng ký) |
| CSS | `public/css/app.css`, `public/css/auth.css` | CSS thuần, theme tối, màu nhấn `#e50914` |
| Lịch chạy | `routes/console.php` | `bookings:expire` chạy mỗi phút |

## Quy tắc quan trọng
1. **Chống đặt trùng ghế:** bảng `booking_seats` có `UNIQUE(active_showtime_id, seat_id)`.
   - Ghế đang giữ hoặc đã bán: `active_showtime_id = showtime_id`. Đơn hủy hoặc hết hạn: `NULL`.
   - Model `Booking` tự cập nhật cột này khi đổi `status` (hook `updated`). Nếu update hàng loạt bằng query builder thì phải tự cập nhật `booking_seats`, như cách `expireStale()` đang làm.
   - Không xóa dòng `booking_seats` để nhả ghế; chỉ đặt `active_showtime_id = NULL`.
2. **Giá vé** = `showtimes.base_price` + `SeatType::surcharge()`. Giá được chốt vào `booking_seats.price` lúc đặt.
3. **Query phải chạy được trên cả SQLite và PostgreSQL:** dùng `whereRaw('LOWER(col) LIKE ?')` thay cho `ilike`, `DATE(col)` để nhóm theo ngày, không dùng hàm riêng của MySQL.
4. **Migration mới** phải có `down()`. Migration `2026_11_13_000099_enable_rls_for_supabase` bật RLS cho mọi bảng trong `public`. Bảng tạo sau migration này thì cần thêm một migration bật RLS cho bảng đó.
5. Cột `role` **không** nằm trong `$fillable` của `User`. Đổi quyền thì dùng `forceFill`.
6. Không xóa cứng phim (dùng `SoftDeletes`), cũng không xóa rạp, phòng hay suất chiếu đã có đơn. Controller đã chặn các trường hợp này, giữ nguyên hành vi đó.
7. Giao diện và thông báo viết bằng **tiếng Việt**. Lỗi validate dịch trong `lang/vi/validation.php` (thêm tên trường mới vào mục `attributes`). Hiển thị tiền bằng directive `@money($x)`.
8. Không commit `.env`, `vendor/`, `database/*.sqlite`, `composer.lock`.

## Thêm tính năng: checklist
- Migration (có `down()`) → Model (`$fillable`, `casts`, quan hệ) → Service nếu có nghiệp vụ → Controller → Route (đúng nhóm middleware) → View (kế thừa layout phù hợp) → cập nhật seeder và README nếu cần.
- Kiểm tra: `php artisan migrate:fresh --seed`, rồi mở thử các trang liên quan với cả 3 tài khoản mẫu.

## Chuyển sang Supabase
Trong `.env`: `DB_CONNECTION=pgsql`, `DB_URL="postgresql://postgres.<REF>:<PASS>@aws-0-<REGION>.pooler.supabase.com:5432/postgres?sslmode=require"`.
Mật khẩu có ký tự đặc biệt thì URL-encode. Sau đó chạy `php artisan migrate:fresh --seed`. PHP cần bật `pdo_pgsql`.

## Triển khai
- `Dockerfile` + `docker/start.sh` + `render.yaml`: deploy lên Render (free, Docker). SQLite được seed lại mỗi lần khởi động; đặt `DB_CONNECTION=pgsql` + `DB_URL` để dùng Supabase.
- `APP_DEMO=true` hiện tài khoản mẫu ở trang đăng nhập ngay cả trên production.
- `bootstrap/app.php` có `trustProxies(at: '*')` để chạy đúng sau proxy HTTPS (Render, Codespaces).
- `site/`: trang giới thiệu tĩnh, deploy lên GitHub Pages bằng `.github/workflows/pages.yml`.
