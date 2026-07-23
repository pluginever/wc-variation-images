<?php

namespace PluginEver\VariationImages\B8\Services;

use PluginEver\VariationImages\B8\App;
defined('ABSPATH') || exit;
/**
 * Handles admin notices.
 *
 * Registers, displays, and persists admin notices, storing per-user
 * dismissals in user meta so dismissed notices stay hidden.
 *
 * @since 1.0.0
 * @package \B8
 */
class Notices
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected App $app;
    /**
     * Ajax action for notice dismissal.
     *
     * @since 1.0.0
     * @var string
     */
    protected string $ajax_action;
    /**
     * Script handle.
     *
     * @since 1.0.0
     * @var string
     */
    protected string $script_handle;
    /**
     * The notices.
     *
     * @since 1.0.0
     * @var array<string, array<string, mixed>>
     */
    protected array $notices = array();
    /**
     * Option key for dismissed notices.
     *
     * @since 1.0.0
     * @var string
     */
    protected string $dismissed_key;
    /**
     * Constructor.
     *
     * @since 1.0.0
     * @param App $app Application instance.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->ajax_action = $this->app->hook_prefix . '_dismiss_notice';
        $this->script_handle = $this->app->short_name . '-dismiss-notices';
        $this->dismissed_key = $this->app->option_prefix . '_dismissed_notices';
    }
    /**
     * Register hooks.
     *
     * @since 1.0.0
     * @return void
     */
    public function register(): void
    {
        add_action('admin_notices', array($this, 'render_notices'));
        add_action('admin_footer', array($this, 'render_notices'));
        add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
        add_action('wp_ajax_' . $this->ajax_action, array($this, 'ajax_dismiss_notice'));
    }
    /**
     * Get the active notices.
     *
     * Returns notices filtered by should_display() with processed messages.
     * Useful for REST API responses in React-based admin pages.
     *
     * @since 1.0.0
     * @return array<int, array<string, mixed>> Array of prepared notice objects.
     */
    public function get_notices(): array
    {
        $prepared = array();
        foreach ($this->notices as $id => $notice) {
            if (!$this->should_display($notice)) {
                continue;
            }
            unset($this->notices[$id]);
            $message = $notice['message'];
            if (substr($message, -4) === '.php') {
                $path = wp_normalize_path($message);
                $real_path = realpath($path);
                $plugin_path = realpath($this->app->plugin_path());
                if ($real_path && $plugin_path && str_starts_with($real_path, $plugin_path)) {
                    ob_start();
                    include $real_path;
                    $message = ob_get_clean();
                }
            }
            if (empty($message)) {
                continue;
            }
            $prepared[] = array('id' => $notice['notice_id'], 'type' => $notice['type'], 'message' => $message, 'dismissible' => $notice['dismissible'], 'class' => $notice['class'], 'style' => $notice['style'], 'nonce' => wp_create_nonce($this->ajax_action), 'action' => $this->ajax_action);
        }
        return $prepared;
    }
    /**
     * Render the notices.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_notices(): void
    {
        $notices = $this->get_notices();
        if (empty($notices)) {
            return;
        }
        wp_enqueue_script($this->script_handle);
        wp_enqueue_style('b8-components');
        foreach ($notices as $notice) {
            $classes = array_filter(wp_parse_list($notice['class']));
            $message = $notice['message'];
            if ($notice['dismissible']) {
                $classes[] = 'is-dismissible';
            }
            if (!preg_match('/<[^>]+>/', $message)) {
                $message = wpautop($message);
            }
            printf('<div class="notice b8-notice notice-%1$s %2$s" data-notice_id="%3$s" data-nonce="%4$s" data-action="%5$s" style="%6$s">%7$s%8$s</div>', esc_attr($notice['type']), esc_attr(implode(' ', $classes)), esc_attr($notice['id']), esc_attr($notice['nonce']), esc_attr($notice['action']), esc_attr($notice['style']), wp_kses_post(wptexturize($message)), $notice['dismissible'] ? '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice</span></button>' : '');
        }
    }
    /**
     * Register the scripts.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_scripts(): void
    {
        wp_register_script($this->script_handle, false, array('jquery'), $this->app->version, true);
        ob_start();
        ?>
		<script>
		jQuery(($) => {
			$('.notice.b8-notice').on('click', '.notice-dismiss, [data-dismiss], [data-snooze]', function (e) {
				e.preventDefault();
				const $notice = $(this).closest('.notice');
				$.post(ajaxurl, $notice.data(), (r) => r.success && $notice.fadeOut());
			});
		});
		</script>
		<?php 
        $script = preg_replace('/<\/?script[^>]*>/', '', ob_get_clean());
        wp_add_inline_script($this->script_handle, trim($script), 'after');
    }
    /**
     * Handle the dismiss notice request.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_dismiss_notice(): void
    {
        if (!check_ajax_referer($this->ajax_action, 'nonce', false) || !is_user_logged_in()) {
            wp_send_json_error('Invalid request');
        }
        $notice_id = isset($_POST['notice_id']) ? sanitize_text_field(wp_unslash($_POST['notice_id'])) : '';
        if (empty($notice_id)) {
            wp_send_json_error();
        }
        $this->dismiss($notice_id);
        wp_send_json_success();
    }
    /**
     * Add a notice.
     *
     * @since 1.0.0
     *
     * @param string|array<string, mixed> $args The notice arguments or message.
     *
     * @return bool True if the notice was added.
     */
    public function add($args): bool
    {
        $args = wp_parse_args(is_string($args) ? array('message' => $args) : $args, array('message' => '', 'type' => 'info', 'dismissible' => true, 'capability' => 'manage_options', 'notice_id' => '', 'class' => '', 'style' => '', 'start' => '', 'end' => ''));
        if (empty($args['message'])) {
            return false;
        }
        if (empty($args['notice_id'])) {
            $args['notice_id'] = $this->app->option_prefix . '_' . md5($args['message'] . $args['type']);
        }
        // Recurring notices: append period suffix so dismiss resets (yearly: _2025, monthly: _2025-01).
        if (strpos($args['start'], 'recurring:') === 0 || strpos($args['end'], 'recurring:') === 0) {
            $part = substr(strpos($args['start'], 'recurring:') === 0 ? $args['start'] : $args['end'], 10);
            $args['notice_id'] .= strpos($part, '-') !== false ? '_' . current_time('Y') : '_' . current_time('Y-m');
        }
        if (filter_var($args['dismissible'], FILTER_VALIDATE_BOOLEAN) && $this->is_dismissed($args['notice_id'])) {
            return false;
        }
        $this->notices[$args['notice_id']] = $args;
        return true;
    }
    /**
     * Whether a notice is dismissed.
     *
     * @since 1.0.0
     *
     * @param string $id The notice ID.
     *
     * @return bool
     */
    public function is_dismissed(string $id): bool
    {
        return in_array($id, get_option($this->dismissed_key, array()), true);
    }
    /**
     * Whether a notice should be displayed.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $notice The notice options.
     *
     * @return bool
     */
    public function should_display(array $notice): bool
    {
        if ($this->is_dismissed($notice['notice_id'])) {
            return false;
        }
        if ($notice['capability'] && !current_user_can($notice['capability'])) {
            return false;
        }
        if (empty($notice['start']) && empty($notice['end'])) {
            return true;
        }
        $now = current_time('Y-m-d');
        $year = current_time('Y');
        $month = current_time('Y-m');
        $start = $notice['start'];
        $end = $notice['end'];
        // Parse recurring: recurring:MM-DD (yearly) or recurring:DD (monthly).
        if (strpos($start, 'recurring:') === 0) {
            $part = substr($start, 10);
            $start = (strpos($part, '-') !== false ? $year : $month) . '-' . $part;
        }
        if (strpos($end, 'recurring:') === 0) {
            $part = substr($end, 10);
            $end = (strpos($part, '-') !== false ? $year : $month) . '-' . $part;
        }
        // Year/month wrap: show if now >= start OR now <= end.
        if ($start && $end && $end < $start) {
            return $now >= $start || $now <= $end;
        }
        return (empty($start) || $now >= $start) && (empty($end) || $now <= $end);
    }
    /**
     * Dismiss a notice.
     *
     * @since 1.0.0
     *
     * @param string $id The notice ID.
     *
     * @return void
     */
    public function dismiss(string $id): void
    {
        $dismissed = get_option($this->dismissed_key, array());
        if (!in_array($id, $dismissed, true)) {
            $dismissed[] = $id;
            update_option($this->dismissed_key, $dismissed);
        }
    }
}