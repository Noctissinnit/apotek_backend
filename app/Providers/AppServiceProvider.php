<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // @rupiah($angka) -> Rp 12.500
        Blade::directive('rupiah', fn (string $ekspresi) => "<?php echo 'Rp '.number_format((float) ($ekspresi), 0, ',', '.'); ?>");

        // Tahan tebak-tebakan password: dihitung per kombinasi email + IP.
        RateLimiter::for('login', function (Request $request) {
            $kunci = Str::lower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinute(config('apotek.login_rate_limit'))
                ->by($kunci)
                ->response(fn () => back()
                    ->onlyInput('email')
                    ->withErrors(['email' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.']));
        });
    }
}
