<?php

namespace App\Http\Controllers\Front;

use Artisan;
use App\Http\Controllers\BaseController;

class SystemCareController extends BaseController
{
    public function complexOptimize()
    {
        $this->clearStat();

        $this->artisanCall('view:clear');
        $this->artisanCall('cache:clear');
        $this->artisanCall('config:cache');
        $this->artisanCall('route:cache');

        $this->clearXCache();
        $this->clearOpCache();

        return redirect()->back()->withStatus(__('common.msg.complete'));
    }

    public function clearStat()
    {
        clearstatcache();
    }

    public function clearCache(string $key = null)
    {
        if (empty($key)) {
            return redirect()->back()->withStatus(
                $this->artisanCall('cache:clear')
            );
        }

        foreach (explode('|', $key) as $k) {
            cache()->forget($k);
        }

        if (! request()->ajax()) {
            return redirect()->back()->withStatus(__('common.msg.cache_clear'));
        }
    }

    public function clearViews()
    {
        return redirect()->back()->withStatus(
            $this->artisanCall('view:clear')
        );
    }

    public function clearXCache()
    {
        if (function_exists('xcache_get')) {
            xcache_clear_cache(XC_TYPE_PHP);
        }
    }

    public function clearOpCache()
    {
        if (function_exists('opcache_invalidate')) {
            collect(\File::allFiles([
                base_path('app'),
                base_path('bootstrap'),
                base_path('resources'),
                base_path('storage'.DS.'framework'.DS.'views'),
            ]))->filter(function ($value) {
                return \File::extension($value) == 'php';
            })->each(function ($file) {
                opcache_invalidate ($file, true);
            });
        }
    }

    protected function artisanCall(string $name)
    {
        Artisan::call($name);

        return Artisan::output();
    }
}
