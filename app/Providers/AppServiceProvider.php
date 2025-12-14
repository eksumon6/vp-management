<?php
/* One more thing I have forgotten to mention. Any table used in the application should use backend datatable and form. dropdown, or select items should have bachkend ajax auto complete suggestion. */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->singleton('calendar', function () {
            return new class {
                public function currentBanglaYear(): int {
                    $today = now('Asia/Dhaka');
                    $gy    = (int)$today->year;
                    $newYearStart = Carbon::create($gy, 4, 14, 0, 0, 0, 'Asia/Dhaka');
                    $base = $gy - 593; // approx
                    return $today->lt($newYearStart) ? $base - 1 : $base;
                }
            };
        });
    }
}
