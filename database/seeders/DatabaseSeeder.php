<?php

namespace Database\Seeders;

use App\Enums\MovieStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Tài khoản mẫu (mật khẩu: password) ---
        foreach ([
            ['Quản trị viên', 'admin@cinema.test', UserRole::Admin],
            ['Nhân viên soát vé', 'staff@cinema.test', UserRole::Staff],
            ['Khách hàng', 'user@cinema.test', UserRole::Customer],
        ] as [$name, $email, $role]) {
            User::create(['name' => $name, 'email' => $email, 'password' => 'password'])
                ->forceFill(['role' => $role, 'email_verified_at' => now()])->save();
        }

        // --- Thể loại ---
        $categories = collect(['Hành động', 'Hoạt hình', 'Kinh dị', 'Tình cảm', 'Hài', 'Khoa học viễn tưởng'])
            ->mapWithKeys(fn ($name) => [$name => Category::create(['name' => $name, 'slug' => Str::slug($name)])]);

        // --- Phim (tên giả lập) ---
        $movies = collect([
            ['Biệt Đội Bão Lửa', 128, 'T16', MovieStatus::NowShowing, true, ['Hành động', 'Khoa học viễn tưởng']],
            ['Chú Mèo Phi Hành Gia', 95, 'P', MovieStatus::NowShowing, true, ['Hoạt hình', 'Hài']],
            ['Căn Nhà Cuối Hẻm', 102, 'T18', MovieStatus::NowShowing, false, ['Kinh dị']],
            ['Mùa Hè Ở Đà Lạt', 110, 'T13', MovieStatus::NowShowing, false, ['Tình cảm']],
            ['Thành Phố Không Ngủ', 135, 'T16', MovieStatus::ComingSoon, true, ['Hành động']],
            ['Bố Ơi Mình Đi Đâu', 98, 'P', MovieStatus::ComingSoon, false, ['Hài', 'Tình cảm']],
        ])->map(function ($m) use ($categories) {
            [$title, $duration, $age, $status, $featured, $cats] = $m;
            $movie = Movie::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => "{$title} là phim mẫu dùng để thử chức năng đặt vé. Admin có thể sửa nội dung, poster và trailer trong trang quản trị.",
                'director' => 'Đạo diễn mẫu',
                'duration' => $duration,
                'age_rating' => $age,
                'language' => 'Tiếng Việt',
                'release_date' => $status === MovieStatus::NowShowing ? now()->subDays(7) : now()->addDays(20),
                'status' => $status,
                'is_featured' => $featured,
                'poster_url' => '/images/posters/'.Str::slug($title).'.svg',
            ]);
            $movie->categories()->attach($categories->only($cats)->pluck('id'));

            return $movie;
        });

        // --- Rạp, phòng, ghế ---
        $rooms = collect();
        foreach ([['CineBox Quận 1', 'TP. Hồ Chí Minh'], ['CineBox Cầu Giấy', 'Hà Nội']] as [$name, $city]) {
            $cinema = Cinema::create(['name' => $name, 'address' => "123 Đường Mẫu, {$city}", 'city' => $city]);
            foreach (['Phòng 1' => [8, 12], 'Phòng 2' => [10, 14]] as $roomName => [$rows, $perRow]) {
                $room = $cinema->rooms()->create([
                    'name' => $roomName, 'format' => '2D', 'total_rows' => $rows, 'seats_per_row' => $perRow,
                ]);
                $room->generateSeats();
                $rooms->push($room);
            }
        }

        // --- Lịch chiếu 7 ngày tới cho phim đang chiếu ---
        $nowShowing = $movies->filter(fn ($m) => $m->status === MovieStatus::NowShowing)->values();
        $slots = ['09:00', '12:30', '16:00', '19:30', '22:30'];
        for ($day = 0; $day < 7; $day++) {
            foreach ($rooms as $i => $room) {
                foreach ($slots as $j => $time) {
                    $movie = $nowShowing[($i + $j + $day) % $nowShowing->count()];
                    $start = Carbon::today()->addDays($day)->setTimeFromTimeString($time);
                    Showtime::create([
                        'movie_id' => $movie->id,
                        'room_id' => $room->id,
                        'start_time' => $start,
                        'end_time' => $start->copy()->addMinutes($movie->duration + 15), // 15' dọn phòng
                        'base_price' => $start->hour >= 17 || $start->isWeekend() ? 90000 : 70000,
                    ]);
                }
            }
        }
    }
}
