<?php

namespace PluginEver\VariationImages\B8\Traits;

defined('ABSPATH') || exit;
/**
 * Pathable trait.
 *
 * Resolves filesystem paths and URLs relative to the plugin root,
 * including the assets, build, and template directories.
 *
 * @since 1.0.0
 * @package \B8
 */
trait PathableTrait
{
    /**
     * Get the plugin basename.
     *
     * @since 1.0.0
     * @return string The plugin basename.
     */
    public function basename(): string
    {
        return plugin_basename($this->file);
    }
    /**
     * Get the plugin path.
     *
     * @since 1.0.0
     * @param string $path Optional. Path relative to the plugin directory.
     * @return string
     */
    public function plugin_path($path = ''): string
    {
        return $this->join_path(plugin_dir_path($this->file), $path);
    }
    /**
     * Get the plugin URL.
     *
     * @since 1.0.0
     * @param string $path Optional. Path relative to the plugin directory.
     * @return string
     */
    public function plugin_url($path = ''): string
    {
        return $this->join_path(plugin_dir_url($this->file), $path);
    }
    /**
     * Get the assets path.
     *
     * @since 1.0.0
     * @param string $path Optional. Path relative to the assets directory.
     * @return string
     */
    public function assets_path($path = ''): string
    {
        return $this->join_path($this->plugin_path(), $this->assets_dir, $path);
    }
    /**
     * Get the assets URL.
     *
     * @since 1.0.0
     * @param string $path Optional. Path relative to the assets directory.
     * @return string
     */
    public function assets_url($path = ''): string
    {
        return $this->join_path($this->plugin_url(), $this->assets_dir, $path);
    }
    /**
     * Get the templates path.
     *
     * @since 1.0.0
     * @param string $path Optional. Path relative to the templates directory.
     * @return string
     */
    public function templates_path($path = ''): string
    {
        return $this->join_path($this->plugin_path(), $this->templates_dir, $path);
    }
    /**
     * Join the path segments.
     *
     * @since 1.0.0
     * @param string $base     Base path or URL.
     * @param string ...$segments Path segments to append.
     * @return string The joined path.
     */
    protected function join_path(string $base, string ...$segments): string
    {
        $path = rtrim($base, '/');
        foreach ($segments as $segment) {
            if ('' !== $segment) {
                $path .= '/' . ltrim($segment, '/');
            }
        }
        return $path;
    }
}