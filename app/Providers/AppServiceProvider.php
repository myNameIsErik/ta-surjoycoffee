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

        Blade::directive('qty', function ($expression) {
            return "<?php \$__v = (float)($expression); echo rtrim(rtrim(number_format(\$__v, 2, ',', '.'), '0'), ','); ?>";
        });

        Blade::directive('rupiah', function ($expression) {
            return "<?php echo 'Rp ' . number_format((float)($expression), 0, ',', '.'); ?>";
        });
    }
}
