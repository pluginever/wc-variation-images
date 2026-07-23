<?php

namespace PluginEver\VariationImages\B8\Services;

use PluginEver\VariationImages\B8\App;
defined('ABSPATH') || exit;
/**
 * Handles caching.
 *
 * Provides a unified interface for transients and object cache with
 * automatic prefixing and expiration handling.
 *
 * @since 1.0.0
 * @package \B8
 */
class Cache
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected $app;
    /**
     * Default cache expiration time in seconds.
     *
     * @since 1.0.0
     * @var int
     */
    protected $lifetime = 3600;
    /**
     * Cache key prefix.
     *
     * @since 1.0.0
     * @var string
     */
    protected $prefix = '';
    /**
     * Constructor.
     *
     * @since 1.0.0
     * @param App $app Application instance.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->prefix = $this->app->cache_group . '_cache_';
        $this->lifetime = $this->app->cache_ttl;
    }
    /**
     * Add a cache entry.
     *
     * @since 1.0.0
     *
     * @param string $key The cache key.
     * @param mixed  $data The data to cache.
     * @param int    $expiration Expiration time in seconds.
     *
     * @return bool True if added, false if key exists or on failure.
     */
    public function add($key, $data, $expiration = 0): bool
    {
        if ($this->has($key)) {
            return false;
        }
        return $this->set($key, $data, $expiration);
    }
    /**
     * Set a cache entry.
     *
     * @since 1.0.0
     *
     * @param string $key The cache key.
     * @param mixed  $data The data to cache.
     * @param int    $expiration Expiration time in seconds.
     *
     * @return bool True on success, false on failure.
     */
    public function set($key, $data, $expiration = 0): bool
    {
        $expiration = 0 === $expiration ? $this->lifetime : $expiration;
        $cache_key = $this->get_key($key);
        $payload = array($data);
        return wp_using_ext_object_cache() ? wp_cache_set($cache_key, $payload, $this->app->cache_group, $expiration) : set_transient($cache_key, $payload, $expiration);
    }
    /**
     * Get a cache entry.
     *
     * @since 1.0.0
     *
     * @param string $key The cache key.
     * @param mixed  $fallback Default value if the cache misses.
     *
     * @return mixed The cached data or the default value.
     */
    public function get($key, $fallback = null)
    {
        $cache_key = $this->get_key($key);
        if (wp_using_ext_object_cache()) {
            $data = wp_cache_get($cache_key, $this->app->cache_group);
        } else {
            $data = get_transient($cache_key);
        }
        // A wrapped value (array with key 0) is a hit; a raw miss returns the fallback.
        return is_array($data) && array_key_exists(0, $data) ? $data[0] : $fallback;
    }
    /**
     * Whether a cache key exists.
     *
     * @since 1.0.0
     *
     * @param string $key The cache key.
     *
     * @return bool True if exists, false otherwise.
     */
    public function has($key): bool
    {
        $sentinel = new \stdClass();
        return $sentinel !== $this->get($key, $sentinel);
    }
    /**
     * Delete a cache entry.
     *
     * @since 1.0.0
     *
     * @param string $key The cache key.
     *
     * @return bool True on success, false on failure.
     */
    public function delete($key): bool
    {
        $cache_key = $this->get_key($key);
        if (wp_using_ext_object_cache()) {
            return wp_cache_delete($cache_key, $this->app->cache_group);
        }
        return delete_transient($cache_key);
    }
    /**
     * Remember a cache entry.
     *
     * If the cache exists, returns the cached data. Otherwise, executes the callback,
     * caches the result, and returns it.
     *
     * @since 1.0.0
     *
     * @param string   $key The cache key.
     * @param callable $callback Callback to generate data on a cache miss.
     * @param int      $expiration Expiration time in seconds.
     *
     * @return mixed The cached or generated data.
     */
    public function remember($key, $callback, $expiration = 0)
    {
        $sentinel = new \stdClass();
        $data = $this->get($key, $sentinel);
        if ($sentinel !== $data) {
            return $data;
        }
        $data = call_user_func($callback);
        $this->set($key, $data, $expiration);
        return $data;
    }
    /**
     * Increment a cache value.
     *
     * @since 1.0.0
     *
     * @param string $key The cache key.
     * @param int    $offset The increment value.
     *
     * @return int|false The new value on success, false on failure.
     */
    public function increment($key, $offset = 1)
    {
        $value = $this->get($key, 0);
        if (!is_numeric($value)) {
            return false;
        }
        $new_value = (int) $value + $offset;
        $this->set($key, $new_value);
        return $new_value;
    }
    /**
     * Decrement a cache value.
     *
     * @since 1.0.0
     *
     * @param string $key The cache key.
     * @param int    $offset The decrement value.
     *
     * @return int|false The new value on success, false on failure.
     */
    public function decrement($key, $offset = 1)
    {
        return $this->increment($key, -$offset);
    }
    /**
     * Flush the cache.
     *
     * Note: This only works reliably with an external object cache.
     * With transients, it clears plugin-specific transients from the database.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure.
     */
    public function flush(): bool
    {
        if (wp_using_ext_object_cache()) {
            return wp_cache_flush_group($this->app->cache_group);
        }
        global $wpdb;
        $prefix = $this->prefix . '%';
        $query = $wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", '_transient_' . $prefix, '_transient_timeout_' . $prefix);
        return false !== $wpdb->query($query);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- bulk transient cleanup by prefix; no core API and caching a flush is moot.
    }
    /**
     * Get the prefixed cache key.
     *
     * @since 1.0.0
     *
     * @param string $key The cache key.
     *
     * @return string The prefixed cache key.
     */
    protected function get_key($key): string
    {
        return $this->prefix . md5($key);
    }
}