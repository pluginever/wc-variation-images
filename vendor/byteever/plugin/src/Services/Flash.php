<?php

namespace PluginEver\VariationImages\B8\Services;

use PluginEver\VariationImages\B8\App;
defined('ABSPATH') || exit;
/**
 * Handles flash messages.
 *
 * Handles temporary messages across page redirects using WordPress user meta
 * and URL parameters for display control.
 *
 * @since 1.0.0
 * @package \B8
 */
class Flash
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected $app;
    /**
     * User meta key for storing flash messages.
     *
     * @since 1.0.0
     * @var string
     */
    protected $meta_key;
    /**
     * Query parameter to trigger flash messages.
     *
     * @since 1.0.0
     * @var string
     */
    protected $query_param = 'flash';
    /**
     * Messages to display.
     *
     * @since 1.0.0
     * @var array<string, array<string, mixed>>
     */
    protected $messages = array();
    /**
     * Constructor.
     *
     * @since 1.0.0
     * @param App $app Application instance.
     */
    public function __construct(App $app)
    {
        $this->messages = array();
        $this->app = $app;
        $this->meta_key = $this->app->option_prefix . '_flash_messages';
        $this->query_param = $this->app->option_prefix . '_messages';
    }
    /**
     * Register hooks.
     *
     * @since 1.0.0
     * @return void
     */
    public function register(): void
    {
        add_action('admin_init', array($this, 'load_messages'));
        add_action('admin_notices', array($this, 'display_messages'));
        add_action('admin_footer', array($this, 'display_messages'));
        add_filter('wp_redirect', array($this, 'save_messages'));
    }
    /**
     * Load the messages.
     *
     * @since 1.0.0
     * @return void
     */
    public function load_messages(): void
    {
        $flash = filter_input(INPUT_GET, $this->query_param, FILTER_VALIDATE_BOOLEAN);
        $user_id = get_current_user_id();
        if (true === $flash && $user_id > 0) {
            $messages = get_user_meta($user_id, $this->meta_key, true);
            if (!empty($messages) && is_array($messages)) {
                foreach ($messages as $message) {
                    if (isset($message['type'], $message['message'])) {
                        $this->add($message['type'], $message['message']);
                    }
                }
            }
            delete_user_meta($user_id, $this->meta_key);
        }
    }
    /**
     * Display the messages.
     *
     * @since 1.0.0
     * @return void
     */
    public function display_messages(): void
    {
        if (empty($this->messages)) {
            return;
        }
        foreach ($this->messages as $message_id => $message) {
            printf('<div class="notice notice-%1$s is-dismissible">%2$s</div>', esc_attr($message['type']), wp_kses_post(wpautop($message['message'])));
            unset($this->messages[$message_id]);
        }
    }
    /**
     * Save the messages.
     *
     * @param string $location The location to redirect to.
     *
     * @since 1.0.0
     * @return string The modified redirect location.
     */
    public function save_messages($location): string
    {
        $user_id = get_current_user_id();
        if (!empty($this->messages) && $user_id > 0) {
            update_user_meta($user_id, $this->meta_key, array_values($this->messages));
            $location = add_query_arg($this->query_param, 'yes', $location);
        }
        return $location;
    }
    /**
     * Add a success message.
     *
     * @since 1.0.0
     *
     * @param string $message The success message.
     *
     * @return void
     */
    public function success($message): void
    {
        $this->add('success', $message);
    }
    /**
     * Add an info message.
     *
     * @since 1.0.0
     *
     * @param string $message The info message.
     *
     * @return void
     */
    public function info($message): void
    {
        $this->add('info', $message);
    }
    /**
     * Add a warning message.
     *
     * @since 1.0.0
     *
     * @param string $message The warning message.
     *
     * @return void
     */
    public function warning($message): void
    {
        $this->add('warning', $message);
    }
    /**
     * Add an error message.
     *
     * @since 1.0.0
     *
     * @param string $message The error message.
     *
     * @return void
     */
    public function error($message): void
    {
        $this->add('error', $message);
    }
    /**
     * Add a message.
     *
     * @param string $type Message type. Must be 'success', 'info', 'warning', or 'error'.
     * @param string $message The message to add.
     *
     * @since 1.0.0
     * @return void
     */
    public function add($type, $message): void
    {
        $valid_types = array('success', 'info', 'warning', 'error');
        if (empty($message) || !in_array($type, $valid_types, true)) {
            return;
        }
        $id = substr(md5($message . $type . time()), 0, 8);
        $this->messages[$id] = array('message' => $message, 'type' => $type);
    }
    /**
     * Clear the messages.
     *
     * @since 1.0.0
     * @return void
     */
    public function clear(): void
    {
        $this->messages = array();
        $user_id = get_current_user_id();
        if ($user_id > 0) {
            delete_user_meta($user_id, $this->meta_key);
        }
    }
}