<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SettingCache
{
    public const CACHE_KEY = 'shared_settings';
    public const TTL_SECONDS = 60;

    public static function all(): Collection
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            return collect($cached);
        }

        if ($cached instanceof Collection) {
            return $cached;
        }

        if ($cached !== null) {
            Cache::forget(self::CACHE_KEY);
        }

        $settings = self::fresh();
        Cache::put(self::CACHE_KEY, $settings->all(), self::TTL_SECONDS);

        return $settings;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()->get($key, $default);
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function fresh(): Collection
    {
        try {
            if (! Schema::hasTable('settings')) {
                return collect();
            }

            return Setting::query()->pluck('value', 'key');
        } catch (Throwable) {
            return collect();
        }
    }
}