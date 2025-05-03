<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class MasterCache
{
    public static function get(string $key)
    {
        return Cache::get("master_{$key}");
    }

    public static function put(string $key, $value, int $ttl = 3600)
    {
        return Cache::put("master_{$key}", $value, $ttl);
    }

    public static function clear(string $key)
    {
        return Cache::forget("master_{$key}");
    }

    public static function remember(string $key, int $ttl, \Closure $callback)
    {
        return Cache::remember("master_{$key}", $ttl, $callback);
    }

    public static function getOrFetch(string $key, int $ttl, \Closure $callback)
    {
        return static::remember($key, $ttl, $callback);
    }
}
