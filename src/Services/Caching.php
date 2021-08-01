<?php

namespace Jawabapp\Community\Services;

use Illuminate\Support\Facades\Cache;
use Closure;

class Caching
{

    private static function keygen($cacheTags, $cacheKey)
    {

        if (!is_array($cacheTags)) {
            $cacheTags = [$cacheTags];
        }

        $cacheKey = strtolower(implode('_', $cacheTags) . "_{$cacheKey}");

        return [
            $cacheTags,
            $cacheKey
        ];
    }

    public static function doCache($cacheTags, $cacheKey, Closure $callable, $ttl = null)
    {

        if (app()->environment() === 'production') {

            static $data;

            list($cacheTags, $cacheKey) = self::keygen($cacheTags, $cacheKey);

            if (!isset($data[$cacheKey])) {

                if (Cache::tags($cacheTags)->has($cacheKey)) {
                    $cache = Cache::tags($cacheTags)->get($cacheKey);
                } else {

                    $cache = $callable();

                    Cache::tags($cacheTags)->put($cacheKey, $cache, $ttl);
                }

                $data[$cacheKey] = $cache;
            }

            return $data[$cacheKey];
        } else {
            return $callable();
        }
    }

    public static function deleteCacheByTags($cacheTags)
    {
        Cache::tags($cacheTags)->flush();
    }

    public static function deleteCacheByKey($cacheTags, $cacheKey)
    {

        list($cacheTags, $cacheKey) = self::keygen($cacheTags, $cacheKey);

        if (Cache::tags($cacheTags)->has($cacheKey)) {
            Cache::tags($cacheTags)->forget($cacheKey);
        }
    }
}
