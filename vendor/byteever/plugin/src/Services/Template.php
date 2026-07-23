<?php

namespace PluginEver\VariationImages\B8\Services;

use PluginEver\VariationImages\B8\App;
use Exception;
defined('ABSPATH') || exit;
/**
 * Handles template rendering.
 *
 * Provides secure template loading and rendering with data passing
 * capabilities and WordPress integration.
 *
 * @since 1.0.0
 * @package \B8
 */
class Template
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected $app;
    /**
     * Constructor.
     *
     * @since 1.0.0
     * @param App $app Application instance.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    /**
     * Render a template.
     *
     * @param string               $template Template path.
     * @param array<string, mixed> $data Template data.
     * @param string               $base_path Base path for templates (empty uses the default templates path).
     *
     * @since 1.0.0
     * @return void
     * @throws Exception If template file doesn't exist.
     */
    public function render($template, $data = array(), string $base_path = ''): void
    {
        $file_path = $this->resolve_file_path($template, $base_path);
        /**
         * Filters the resolved template file path before rendering.
         *
         * @example
         * add_filter( 'my_plugin_template_path', function( $file_path, $template, $base_path ) {
         *     if ( 'emails.welcome' === $template ) {
         *         return get_stylesheet_directory() . '/my-plugin/emails/welcome.php';
         *     }
         *     return $file_path;
         * }, 10, 3 );
         *
         * @param string $file_path Resolved template file path.
         * @param string $template  Template name in dot notation.
         * @param string $base_path Base path for templates.
         */
        $file_path = $this->app->apply_filters('template_path', $file_path, $template, $base_path);
        if (!$this->app->fs->exists($file_path)) {
            throw new Exception(esc_html("The view file [{$file_path}] doesn't exist!"));
        }
        extract($data, EXTR_SKIP);
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Controlled extraction for templates.
        include $file_path;
    }
    /**
     * Render a view.
     *
     * Same as render() but without the 'template_path' filter, so it cannot be overridden.
     *
     * @param string               $view View name in dot notation.
     * @param array<string, mixed> $data View data.
     * @param string               $base_path Base path for views (empty uses the default templates path).
     *
     * @since 1.0.0
     * @return void
     * @throws Exception If the view file doesn't exist.
     */
    public function view($view, $data = array(), string $base_path = ''): void
    {
        $file_path = $this->resolve_file_path($view, $base_path);
        if (!$this->app->fs->exists($file_path)) {
            throw new Exception(esc_html("The view file [{$file_path}] doesn't exist!"));
        }
        extract($data, EXTR_SKIP);
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Controlled extraction for views.
        include $file_path;
    }
    /**
     * Resolve the template file path.
     *
     * @param string $template Template path with dot notation.
     * @param string $base_path Base path for templates.
     *
     * @since 1.0.0
     * @return string
     */
    protected function resolve_file_path($template, $base_path = ''): string
    {
        $template = str_replace('.', DIRECTORY_SEPARATOR, $template);
        if (empty($base_path)) {
            $base_path = $this->app->templates_path();
        }
        return rtrim($base_path, '/') . '/' . $template . '.php';
    }
}