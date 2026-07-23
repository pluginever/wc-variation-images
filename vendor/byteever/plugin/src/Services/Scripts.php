<?php

namespace PluginEver\VariationImages\B8\Services;

use PluginEver\VariationImages\B8\App;
defined('ABSPATH') || exit;
/**
 * Handles asset registration.
 *
 * Handles registration and enqueuing of scripts and styles, resolving
 * versioned asset URLs and paths from the build directory.
 *
 * @since 1.0.0
 * @package \PluginEver\VariationImages\B8\Services
 */
class Scripts
{
    /**
     * The application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected $app;
    /**
     * Constructor.
     *
     * @param App $app The application instance.
     *
     * @since 1.0.0
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    /**
     * Register a script.
     *
     * @param string             $handle Script handle. Should be unique.
     * @param string             $src Script source path relative to assets directory or absolute URL.
     * @param array<int, string> $deps Array of script dependencies. Default empty array.
     * @param bool               $in_footer Whether to enqueue in footer. Default false.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure.
     */
    public function register_script($handle, $src, $deps = array(), bool $in_footer = false): bool
    {
        $url = $this->get_asset_url($src);
        $path = $this->get_asset_path($src);
        $asset_file = str_replace('.js', '.asset.php', $path);
        $asset_data = file_exists($asset_file) ? require $asset_file : array();
        $asset_data = wp_parse_args($asset_data, array('dependencies' => array(), 'version' => $this->app->version));
        $merged_deps = array_merge($asset_data['dependencies'], $deps);
        $registered = wp_register_script($handle, $url, $merged_deps, $asset_data['version'], $in_footer);
        if ($registered && in_array('wp-i18n', $merged_deps, true)) {
            wp_set_script_translations($handle, $this->app->text_domain, $this->app->plugin_path(ltrim($this->app->domain_path, '/')));
        }
        return $registered;
    }
    /**
     * Register a stylesheet.
     *
     * @param string             $handle Style handle. Should be unique.
     * @param string             $src Style source path relative to assets directory or absolute URL.
     * @param array<int, string> $deps Array of style dependencies. Default empty array.
     * @param string             $media Media type for stylesheet. Default 'all'.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure.
     */
    public function register_style($handle, $src, $deps = array(), string $media = 'all'): bool
    {
        $url = $this->get_asset_url($src);
        $path = $this->get_asset_path($src);
        $asset_file = str_replace('.css', '.asset.php', $path);
        $asset_data = file_exists($asset_file) ? require $asset_file : array();
        $asset_data = wp_parse_args($asset_data, array('version' => $this->app->version));
        $registered = wp_register_style($handle, $url, $deps, $asset_data['version'], $media);
        if ($registered && is_rtl() && file_exists(str_replace('.css', '-rtl.css', $path))) {
            wp_style_add_data($handle, 'rtl', 'replace');
        }
        return $registered;
    }
    /**
     * Enqueue a script.
     *
     * @param string             $handle Script handle.
     * @param string|null        $src Script source path. Required if not already registered.
     * @param array<int, string> $deps Array of script dependencies. Default empty array.
     * @param bool               $in_footer Whether to enqueue in footer. Default false.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure.
     */
    public function enqueue_script($handle, $src = null, $deps = array(), bool $in_footer = false): bool
    {
        if (!wp_script_is($handle, 'registered') && !empty($src)) {
            $this->register_script($handle, $src, $deps, $in_footer);
        }
        if (!wp_script_is($handle, 'registered')) {
            return false;
        }
        wp_enqueue_script($handle);
        return true;
    }
    /**
     * Enqueue a stylesheet.
     *
     * @param string             $handle Style handle.
     * @param string|null        $src Style source path. Required if not already registered.
     * @param array<int, string> $deps Array of style dependencies. Default empty array.
     * @param string             $media Media type for stylesheet. Default 'all'.
     *
     * @since 1.0.0
     * @return bool True on success, false on failure.
     */
    public function enqueue_style($handle, $src = null, $deps = array(), string $media = 'all'): bool
    {
        if (!wp_style_is($handle, 'registered') && !empty($src)) {
            $this->register_style($handle, $src, $deps, $media);
        }
        if (!wp_style_is($handle, 'registered')) {
            return false;
        }
        wp_enqueue_style($handle);
        return true;
    }
    /**
     * Get the asset URL.
     *
     * @param string $src Asset source path or URL.
     *
     * @since 1.0.0
     * @return string Asset URL.
     */
    protected function get_asset_url($src): string
    {
        return preg_match('/^(https?:)?\/\//', $src) ? $src : $this->app->assets_url($this->app->build_dir . '/' . $src);
    }
    /**
     * Get the asset path.
     *
     * @param string $src Asset source path or URL.
     *
     * @since 1.0.0
     * @return string Asset file path.
     */
    protected function get_asset_path($src): string
    {
        return preg_match('/^(https?:)?\/\//', $src) ? str_replace($this->app->plugin_url(), $this->app->plugin_path(), $src) : $this->app->assets_path($this->app->build_dir . '/' . $src);
    }
}