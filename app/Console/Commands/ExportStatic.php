<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Models\Showtime;
use App\Services\BookingService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ViewErrorBag;

/**
 * Xuất website thành HTML tĩnh để đăng lên GitHub Pages.
 *   php artisan export:static --out=build/static --base=/CineBox
 */
class ExportStatic extends Command
{
    protected $signature = 'export:static
        {--out=build/static : Thư mục xuất}
        {--base=/ : Đường dẫn gốc trên host (VD: /CineBox cho GitHub Pages của repo)}
        {--full= : Link chạy bản đầy đủ (Codespaces, server...)}';

    protected $description = 'Xuất website CineBox thành HTML tĩnh (GitHub Pages)';

    private const HOST = 'https://cinebox.export';

    private string $out;

    private string $base;

    public function handle(): int
    {
        $this->out = base_path($this->option('out'));
        $this->base = rtrim('/'.trim($this->option('base'), '/'), '/').'/';

        File::deleteDirectory($this->out);
        File::ensureDirectoryExists($this->out);

        URL::forceRootUrl(self::HOST);
        URL::forceScheme('https');
        config(['app.url' => self::HOST]);

        // 1) Các trang render qua HTTP kernel (khách chưa đăng nhập)
        $pages = [
            '/' => 'index.html',
            '/phim' => 'phim/index.html',
            '/phim?status=coming_soon' => 'phim/sap-chieu.html',
            '/phim?status=all' => 'phim/tat-ca.html',
            '/dang-nhap' => 'dang-nhap/index.html',
            '/dang-ky' => 'dang-ky/index.html',
        ];
        foreach (Movie::all() as $movie) {
            $pages["/phim/{$movie->slug}"] = "phim/{$movie->slug}/index.html";
            for ($i = 0; $i < 7; $i++) {
                $d = today()->addDays($i)->toDateString();
                $pages["/phim/{$movie->slug}?date={$d}"] = "phim/{$movie->slug}/{$d}.html";
            }
        }
        foreach ($pages as $uri => $file) {
            $this->write($file, $this->fetch($uri));
        }

        // 2) Trang chọn ghế (cần đăng nhập) -> render view trực tiếp
        view()->share('errors', new ViewErrorBag);
        $service = app(BookingService::class);
        $count = 0;
        Showtime::with('movie', 'room.cinema', 'room.seats')
            ->where('is_active', true)->where('start_time', '>', now())
            ->get()
            ->each(function (Showtime $st) use ($service, &$count) {
                $html = view('bookings.seats', [
                    'showtime' => $st,
                    'rows' => $st->room->seats->groupBy('row_label'),
                    'taken' => $service->takenSeatIds($st),
                    'maxSeats' => BookingService::MAX_SEATS,
                ])->render();
                $this->write("suat-chieu/{$st->id}/chon-ghe/index.html", $html);
                $count++;
            });

        // 3) Tài nguyên tĩnh
        File::copyDirectory(public_path('css'), "{$this->out}/css");
        File::copyDirectory(public_path('images'), "{$this->out}/images");
        if (File::exists(public_path('favicon.ico'))) {
            File::copy(public_path('favicon.ico'), "{$this->out}/favicon.ico");
        }
        File::put("{$this->out}/.nojekyll", '');
        File::put("{$this->out}/404.html", '<!DOCTYPE html><meta charset="utf-8"><meta http-equiv="refresh" content="0; url='.$this->base.'"><title>CineBox</title><p>Đang chuyển về <a href="'.$this->base.'">trang chủ</a>…</p>');

        $this->info('Đã xuất '.(count($pages) + $count).' trang vào '.$this->option('out'));

        return self::SUCCESS;
    }

    private function fetch(string $uri): string
    {
        $response = app(Kernel::class)->handle(Request::create(self::HOST.$uri, 'GET'));
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException("Không render được {$uri} (HTTP {$response->getStatusCode()})");
        }

        return $response->getContent();
    }

    private function write(string $file, string $html): void
    {
        $html = $this->rewrite($html);
        $path = "{$this->out}/{$file}";
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $html);
    }

    private function rewrite(string $html): string
    {
        // Link tuyệt đối do route()/asset() sinh ra
        $html = preg_replace_callback('~'.preg_quote(self::HOST, '~').'(/[^"\'\s<>)]*)?~',
            fn ($m) => $this->map($m[1] ?? '/'), $html);

        // Đường dẫn gốc dạng /images/... (poster)
        $html = preg_replace('~(["\'(])/(images|css)/~', '$1'.$this->base.'$2/', $html);

        return str_replace('</body>', $this->demoBar().'</body>', $html);
    }

    private function map(string $url): string
    {
        $parts = parse_url($url);
        $path = trim($parts['path'] ?? '/', '/');
        parse_str(html_entity_decode($parts['query'] ?? ''), $q);
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        $target = match (true) {
            $path === '' => '',
            $path === 'phim' => match ($q['status'] ?? 'now_showing') {
                'coming_soon' => 'phim/sap-chieu.html',
                'all' => 'phim/tat-ca.html',
                default => 'phim/',
            },
            (bool) preg_match('~^phim/[^/]+$~', $path) => isset($q['date']) ? "{$path}/{$q['date']}.html" : "{$path}/",
            (bool) preg_match('~\.(css|js|svg|png|jpe?g|ico|webp)$~', $path) => $path,
            default => "{$path}/",
        };

        return $this->base.$target.$fragment;
    }

    private function demoBar(): string
    {
        $full = e($this->option('full') ?: 'https://github.com/Hanny2005/CineBox');

        return <<<HTML
<div id="static-demo-bar" style="position:fixed;left:50%;bottom:16px;transform:translateX(-50%);z-index:2000;max-width:calc(100% - 24px);background:#1c1f27;border:1px solid #e50914;color:#eee;padding:.55rem .9rem;border-radius:12px;font-size:.88rem;box-shadow:0 10px 30px rgba(0,0,0,.5);display:flex;gap:.6rem;align-items:center">
  <span>🎬 Bản xem trước trên GitHub Pages. Đặt vé và đăng nhập cần <a href="{$full}" target="_blank" rel="noopener" style="color:#ff6b73;font-weight:600">chạy bản đầy đủ</a>.</span>
  <button type="button" onclick="this.parentNode.remove()" style="background:none;border:0;color:#999;font-size:1.1rem;line-height:1">×</button>
</div>
<div id="static-demo-modal" style="display:none;position:fixed;inset:0;z-index:2100;background:rgba(0,0,0,.65);align-items:center;justify-content:center;padding:16px">
  <div style="background:#171a21;border:1px solid #2a2f3a;border-radius:16px;max-width:440px;width:100%;padding:24px;color:#eee">
    <h5 style="margin:0 0 .6rem">Đây là bản xem trước</h5>
    <p id="static-demo-msg" style="color:#aab;margin-bottom:1rem"></p>
    <p style="color:#aab;font-size:.9rem">GitHub Pages chỉ lưu trang tĩnh nên không có máy chủ để lưu đơn hay tài khoản. Hãy chạy bản đầy đủ (Laravel) để dùng hết tính năng.</p>
    <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem">
      <button type="button" onclick="document.getElementById('static-demo-modal').style.display='none'" style="background:transparent;border:1px solid #444;color:#ddd;border-radius:10px;padding:.5rem 1rem">Đóng</button>
      <a href="{$full}" target="_blank" rel="noopener" style="background:#e50914;color:#fff;border-radius:10px;padding:.5rem 1rem;text-decoration:none;font-weight:600">Chạy bản đầy đủ</a>
    </div>
  </div>
</div>
<script>
document.addEventListener('submit', function (e) {
  var f = e.target;
  if ((f.getAttribute('method') || 'get').toLowerCase() !== 'post') return;
  e.preventDefault(); e.stopImmediatePropagation();
  var msg = 'Thao tác này cần máy chủ.';
  var seats = f.querySelectorAll('input[name="seats[]"]');
  if (seats.length) {
    var labels = document.getElementById('selectedLabels'), total = document.getElementById('total');
    msg = 'Bạn đã chọn ghế ' + (labels ? labels.textContent : '') + ', tổng ' + (total ? total.textContent : '') + '. Ở bản đầy đủ, ghế sẽ được giữ 10 phút để thanh toán và nhận vé QR.';
  } else if (f.querySelector('input[name="password"]')) {
    msg = 'Đăng nhập và đăng ký chỉ hoạt động ở bản đầy đủ.';
  }
  document.getElementById('static-demo-msg').textContent = msg;
  document.getElementById('static-demo-modal').style.display = 'flex';
}, true);
</script>
HTML;
    }
}
