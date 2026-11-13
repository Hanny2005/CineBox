<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // @money(90000) => 90.000 đ
        Blade::directive('money', fn ($expr) => "<?php echo number_format((int) ($expr), 0, ',', '.') . ' đ'; ?>");
    }
}
