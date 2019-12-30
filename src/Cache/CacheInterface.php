<?php

namespace MonthlyCloud\Sdk\Cache;

interface CacheInterface
{
    /**
     * Retrieve an item from the cache by key.
     *
     * @param string|array $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Store an item in the cache for a given number of seconds (Laravel 5.8+) or minutes.
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl
     *
     * @return void
     */
    public function put($key, $value, $ttl);

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function forget($key);

    /**
     * Check if item exists in cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);
}
