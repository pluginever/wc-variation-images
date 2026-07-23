<?php

namespace PluginEver\VariationImages\B8;

use PluginEver\VariationImages\B8\Container\Container;
use PluginEver\VariationImages\B8\Services\Cache;
use PluginEver\VariationImages\B8\Services\Filesystem;
use PluginEver\VariationImages\B8\Services\Flash;
use PluginEver\VariationImages\B8\Services\Router;
use PluginEver\VariationImages\B8\Services\Logger;
use PluginEver\VariationImages\B8\Services\Notices;
use PluginEver\VariationImages\B8\Services\Options;
use PluginEver\VariationImages\B8\Services\Queue;
use PluginEver\VariationImages\B8\Services\Request;
use PluginEver\VariationImages\B8\Services\Scripts;
use PluginEver\VariationImages\B8\Services\Settings;
use PluginEver\VariationImages\B8\Services\Template;
use PluginEver\VariationImages\B8\Traits\HookableTrait;
use PluginEver\VariationImages\B8\Traits\PathableTrait;
defined('ABSPATH') || exit;
/**
 * Base plugin application.
 *
 * Serves as the base class each plugin extends. Wires up the service
 * container, exposes framework services as read-only properties, and
 * bootstraps the plugin onload.
 *
 * @since 1.0.0
 * @package \B8
 *
 * @property string     $file               Main plugin file path.
 * @property string     $slug               Plugin slug (directory name).
 * @property string     $namespace          Plugin root namespace.
 * @property string     $version            Plugin version.
 * @property string     $short_name         Base alphanumeric identifier used to generate default prefixes (e.g., 'myplugin').
 * @property string     $rest_prefix        REST API namespace prefix.
 * @property string     $rest_version       REST API version (e.g. 'v1'). When set, automatically appended to namespace.
 * @property string     $option_prefix      Database options prefix.
 * @property string     $cache_group        Object cache group identifier.
 * @property string     $hook_prefix        Custom WordPress hooks prefix.
 * @property string     $hook_separator     Separator used when building hook names.
 * @property string     $text_domain        Plugin text domain.
 * @property string     $domain_path        Domain path for translations.
 * @property string     $assets_dir         Assets directory name.
 * @property string     $build_dir          Compiled assets directory name.
 * @property string     $templates_dir      Templates directory name.
 * @property int        $cache_ttl          Cache lifetime in seconds.
 * @property string     $log_level          Minimum log level (default: 'error').
 * @property int        $log_max_size       Maximum log file size in bytes (default: 5MB).
 *
 * @property Router     $router             REST API router.
 * @property Flash      $flash              Flash messages service.
 * @property Logger     $logger             Logger service.
 * @property Notices    $notices            Admin notices service.
 * @property Cache      $cache              Cache service.
 * @property Options    $options            Options service.
 * @property Queue      $queue              Background queue service.
 * @property Filesystem $fs                 Filesystem service.
 * @property Template   $template           Template service.
 * @property Request    $request            Request service.
 * @property Scripts    $scripts            Scripts service.
 * @property Settings   $settings           Settings service.
 */
abstract class App extends Container
{
    use HookableTrait;
    use PathableTrait;
    /**
     * Framework version.
     *
     * @since 1.0.0
     * @var string
     */
    const FW_VERSION = '1.0.0';
    /**
     * Singleton instances.
     *
     * @since 1.0.0
     * @var array<string, self>
     */
    protected static $instances = array();
    /**
     * Create the singleton instance.
     *
     * @since 1.0.0
     * @param string               $file The main plugin file path (__FILE__).
     * @param array<string, mixed> $data Plugin configuration array.
     *
     * @return static The instance of the plugin.
     */
    public static function create($file, array $data = array())
    {
        $p = get_called_class();
        if (!isset(static::$instances[$p])) {
            $data['file'] = $file;
            static::$instances[$p] = new static($data);
        }
        return static::$instances[$p];
    }
    /**
     * Get the singleton instance.
     *
     * @since 1.0.0
     * @return static The instance of the plugin.
     */
    public static function instance(): self
    {
        $p = get_called_class();
        if (!isset(static::$instances[$p])) {
            wp_die('Plugin not initialized.');
        }
        return static::$instances[$p];
    }
    /**
     * Constructor.
     *
     * @since 1.0.0
     * @param array<string, mixed> $data The plugin data.
     */
    protected function __construct($data)
    {
        $this->configure($data);
        $this->preflight();
    }
    /**
     * Configure the application.
     *
     * @since 1.0.0
     * @param array<string, mixed> $data The plugin data.
     * @return void
     */
    protected function configure(array $data): void
    {
        $slug = basename(dirname($data['file']));
        $short_slug = strtolower(preg_replace('/[^a-z0-9]/i', '', $slug));
        $short_name = !empty($data['short_name']) ? $data['short_name'] : $short_slug;
        $class = get_class($this);
        $separator = strrpos($class, '\\');
        $namespace = false !== $separator ? substr($class, 0, $separator) : '';
        $defaults = array('file' => $data['file'], 'slug' => $slug, 'namespace' => $namespace, 'version' => '1.0.0', 'short_name' => $short_name, 'rest_prefix' => $short_name, 'rest_version' => '', 'option_prefix' => $short_name, 'cache_group' => $short_name, 'hook_prefix' => str_replace('-', '_', $slug), 'hook_separator' => '_', 'text_domain' => str_replace('_', '-', $slug), 'domain_path' => '/languages', 'assets_dir' => 'assets', 'build_dir' => 'build', 'templates_dir' => 'templates', 'cache_ttl' => 3600, 'log_level' => 'error', 'log_max_size' => 5 * 1024 * 1024);
        $config = array_merge($defaults, $data);
        foreach ($config as $key => $value) {
            $this->set($key, $value);
        }
        $required = array('file', 'version', 'short_name', 'rest_prefix', 'option_prefix', 'cache_group', 'hook_prefix');
        foreach ($required as $key) {
            if (empty($this->get($key))) {
                wp_die(sprintf('Plugin error: "%s" is required.', esc_html($key)));
            }
        }
    }
    /**
     * Register the framework services.
     *
     * @since 1.0.0
     * @return void
     */
    protected function preflight(): void
    {
        // Register application instance with alias.
        $this->share(static::class, $this);
        $this->alias(static::class, 'app');
        $this->alias(static::class, __CLASS__);
        // Core Services.
        $this->bind('flash', Flash::class);
        $this->bind('logger', Logger::class);
        $this->bind('notices', Notices::class);
        $this->bind('router', Router::class);
        // Utility Services.
        $this->bind('cache', Cache::class);
        $this->bind('fs', Filesystem::class);
        $this->bind('options', Options::class);
        $this->bind('queue', Queue::class);
        $this->bind('request', Request::class);
        $this->bind('scripts', Scripts::class);
        $this->bind('template', Template::class);
        // Prefer the plugin's own Services\Settings when it extends the base.
        $settings = $this->namespace . '\Services\Settings';
        $this->bind('settings', is_subclass_of($settings, Settings::class) ? $settings : Settings::class);
        $this->flash->register();
        $this->notices->register();
        $this->queue->register();
        add_action('init', function () {
            if (wp_style_is('b8-components', 'registered')) {
                return;
            }
            $assets_url = plugin_dir_url(__FILE__) . 'assets/';
            $this->scripts->register_style('b8-components', $assets_url . 'components.css');
            $this->scripts->register_style('b8-layout', $assets_url . 'layout.css');
            $this->scripts->register_script('b8-settings', $assets_url . 'settings.js');
        }, 1);
    }
    /**
     * Bootstrap the plugin.
     *
     * @since 1.0.0
     * @return void
     */
    abstract public function bootstrap(): void;
    /**
     * Boot the components.
     *
     * @since 1.0.0
     * @param array<int|string, class-string> $components Components to boot.
     * @return void
     */
    public function boot(array $components): void
    {
        foreach ($components as $alias => $class) {
            if (is_string($alias)) {
                $this->bind($alias, $class);
                $class = $alias;
            }
            $component = $this->make($class);
            if ($component instanceof Component && $component->autoload()) {
                $component->register();
                $this->boot($component->components);
            }
        }
    }
    /**
     * Whether a plugin is installed.
     *
     * @since 1.0.0
     * @param string $plugin The plugin slug or basename.
     * @return bool
     */
    public function plugin_exists($plugin): bool
    {
        if (!str_contains($plugin, '/')) {
            $plugin = $plugin . '/' . $plugin . '.php';
        }
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins = get_plugins();
        return array_key_exists($plugin, $plugins);
    }
    /**
     * Whether a plugin is active.
     *
     * @since 1.0.0
     * @param string $plugin The plugin slug or basename.
     * @return bool
     */
    public function plugin_active($plugin): bool
    {
        if (!str_contains($plugin, '/')) {
            $plugin = $plugin . '/' . $plugin . '.php';
        }
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array($plugin, $active_plugins, true) || array_key_exists($plugin, $active_plugins);
    }
}