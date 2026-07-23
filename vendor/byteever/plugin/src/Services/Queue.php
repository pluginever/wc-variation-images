<?php

namespace PluginEver\VariationImages\B8\Services;

use PluginEver\VariationImages\B8\App;
defined('ABSPATH') || exit;
/**
 * Handles the background queue.
 *
 * Provides a reliable queue system for background task processing with
 * automatic retries, priority ordering, and scheduled execution. Built
 * on battle-tested patterns from WP Background Processing and Action Scheduler.
 *
 * @since 1.0.0
 * @package \B8
 */
class Queue
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected $app;
    /**
     * Queue identifier prefix.
     *
     * @since 1.0.0
     * @var string
     */
    protected $identifier;
    /**
     * Hook prefix for actions and filters.
     *
     * @since 1.0.0
     * @var string
     */
    protected $hook_prefix;
    /**
     * Maximum number of retry attempts for failed actions.
     *
     * @since 1.0.0
     * @var int
     */
    protected $max_attempts = 3;
    /**
     * Time limit for processing in seconds.
     *
     * @since 1.0.0
     * @var int
     */
    protected $time_limit = 20;
    /**
     * Start time of current batch processing.
     *
     * @since 1.0.0
     * @var int
     */
    protected $start_time = 0;
    /**
     * Constructor.
     *
     * @since 1.0.0
     * @param App $app Application instance.
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->identifier = $this->app->option_prefix . '_queue';
        $this->hook_prefix = $this->app->hook_prefix;
    }
    /**
     * Register hooks.
     *
     * @since 1.0.0
     * @return void
     */
    public function register(): void
    {
        add_action('wp_ajax_' . $this->identifier, array($this, 'maybe_handle'));
        add_action('wp_ajax_nopriv_' . $this->identifier, array($this, 'maybe_handle'));
        add_action('shutdown', array($this, 'maybe_dispatch'));
        add_action($this->identifier . '_cron', array($this, 'handle_cron'));
        add_filter('cron_schedules', array($this, 'add_cron_interval'));
        // phpcs:ignore WordPress.WP.CronInterval.ChangeDetected -- Intentional addition of custom interval.
        add_action('init', array($this, 'schedule_cron'));
    }
    /**
     * Handle the async request.
     *
     * @since 1.0.0
     * @return void
     */
    public function maybe_handle(): void
    {
        session_write_close();
        check_ajax_referer($this->identifier, 'nonce');
        if ($this->is_processing() || $this->is_queue_empty()) {
            wp_die();
        }
        $this->handle();
        wp_die();
    }
    /**
     * Maybe dispatch the async request.
     *
     * @since 1.0.0
     * @return void
     */
    public function maybe_dispatch(): void
    {
        if ($this->is_queue_empty()) {
            return;
        }
        if ($this->is_processing()) {
            return;
        }
        $this->dispatch();
    }
    /**
     * Handle the cron healthcheck.
     *
     * @since 1.0.0
     * @return void
     */
    public function handle_cron(): void
    {
        if ($this->is_processing()) {
            return;
        }
        if ($this->is_queue_empty()) {
            return;
        }
        $this->handle();
    }
    /**
     * Add a custom cron interval.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $schedules Existing cron schedules.
     *
     * @return array<string, mixed> Modified cron schedules.
     */
    public function add_cron_interval($schedules): array
    {
        /**
         * Filters the queue cron interval in seconds.
         *
         * @since 1.0.0
         *
         * @param int $interval Interval in seconds. Default 300 (5 minutes).
         */
        $interval = apply_filters($this->hook_prefix . '_queue_cron_interval', 300);
        $schedules[$this->identifier . '_interval'] = array('interval' => $interval, 'display' => 'Every 5 minutes');
        return $schedules;
    }
    /**
     * Schedule the cron event.
     *
     * @since 1.0.0
     * @return void
     */
    public function schedule_cron(): void
    {
        if (!empty($this->get_tasks()) && !wp_next_scheduled($this->identifier . '_cron')) {
            wp_schedule_event(time(), $this->identifier . '_interval', $this->identifier . '_cron');
        }
    }
    /**
     * Add an action.
     *
     * @since 1.0.0
     *
     * @param string               $hook Hook name to trigger.
     * @param array<string, mixed> $args Arguments to pass to hook.
     * @param string               $group Optional. Action group identifier.
     *
     * @return string Action ID on success, empty string on failure.
     */
    public function add($hook, $args = array(), string $group = ''): string
    {
        return $this->push(time(), $hook, $args, $group);
    }
    /**
     * Schedule an action.
     *
     * @since 1.0.0
     *
     * @param int                  $timestamp Unix timestamp for when to run.
     * @param string               $hook Hook name to trigger.
     * @param array<string, mixed> $args Arguments to pass to hook.
     * @param string               $group Optional. Action group identifier.
     *
     * @return string Action ID on success, empty string on failure.
     */
    public function schedule($timestamp, $hook, $args = array(), string $group = ''): string
    {
        return $this->push($timestamp, $hook, $args, $group);
    }
    /**
     * Whether an action is scheduled.
     *
     * @since 1.0.0
     *
     * @param string               $hook Hook name.
     * @param array<string, mixed> $args Arguments to match.
     * @param string               $group Group to match.
     *
     * @return bool True if scheduled, false otherwise.
     */
    public function is_scheduled($hook, $args = array(), string $group = ''): bool
    {
        $data = $this->get_queue_data();
        $actions = $data['actions'] ?? array();
        foreach ($actions as $action) {
            if ($this->action_matches($action, $hook, $args, $group)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Get the next scheduled time.
     *
     * @since 1.0.0
     *
     * @param string               $hook Hook name.
     * @param array<string, mixed> $args Arguments to match.
     * @param string               $group Group to match.
     *
     * @return int|null Unix timestamp or null if not found.
     */
    public function get_next($hook, $args = array(), string $group = ''): ?int
    {
        $data = $this->get_queue_data();
        $actions = $data['actions'] ?? array();
        foreach ($actions as $action) {
            if ($this->action_matches($action, $hook, $args, $group)) {
                return $action['schedule'];
            }
        }
        return null;
    }
    /**
     * Get the pending actions.
     *
     * @since 1.0.0
     *
     * @param string $group Optional. Filter by group.
     *
     * @return array<int, array<string, mixed>> Array of action data.
     */
    public function get_tasks($group = ''): array
    {
        $data = $this->get_queue_data();
        $actions = $data['actions'] ?? array();
        if (!empty($group)) {
            $actions = array_filter($actions, function ($action) use ($group) {
                return $action['group'] === $group;
            });
        }
        return array_values($actions);
    }
    /**
     * Get an action by ID.
     *
     * @since 1.0.0
     *
     * @param string $action_id Action ID.
     *
     * @return array<string, mixed>|null Action data or null if not found.
     */
    public function get_task($action_id): ?array
    {
        $data = $this->get_queue_data();
        $actions = $data['actions'] ?? array();
        foreach ($actions as $action) {
            if ($action['id'] === $action_id) {
                return $action;
            }
        }
        return null;
    }
    /**
     * Cancel an action.
     *
     * @since 1.0.0
     *
     * @param string               $hook Hook name.
     * @param array<string, mixed> $args Arguments to match.
     * @param string               $group Group to match.
     *
     * @return void
     */
    public function cancel($hook, $args = array(), string $group = ''): void
    {
        $data = $this->get_queue_data();
        $actions = $data['actions'] ?? array();
        foreach ($actions as $index => $action) {
            if ($this->action_matches($action, $hook, $args, $group)) {
                unset($actions[$index]);
                break;
            }
        }
        $data['actions'] = array_values($actions);
        $this->update_queue_data($data);
    }
    /**
     * Cancel all matching actions.
     *
     * @since 1.0.0
     *
     * @param string               $hook Hook name.
     * @param array<string, mixed> $args Arguments to match.
     * @param string               $group Group to match.
     *
     * @return void
     */
    public function cancel_all($hook, $args = array(), string $group = ''): void
    {
        $data = $this->get_queue_data();
        $actions = $data['actions'] ?? array();
        $actions = array_filter($actions, function ($action) use ($hook, $args, $group) {
            return !$this->action_matches($action, $hook, $args, $group);
        });
        $data['actions'] = array_values($actions);
        $this->update_queue_data($data);
    }
    /**
     * Cancel an action by ID.
     *
     * @since 1.0.0
     *
     * @param string $action_id Action ID.
     *
     * @return bool True if cancelled, false if not found.
     */
    public function cancel_task($action_id): bool
    {
        $data = $this->get_queue_data();
        $actions = $data['actions'] ?? array();
        foreach ($actions as $index => $action) {
            if ($action['id'] === $action_id) {
                unset($actions[$index]);
                $data['actions'] = array_values($actions);
                $this->update_queue_data($data);
                return true;
            }
        }
        return false;
    }
    /**
     * Clear the queue.
     *
     * @since 1.0.0
     * @return void
     */
    public function clear(): void
    {
        delete_option($this->identifier);
        wp_clear_scheduled_hook($this->identifier . '_cron');
    }
    /**
     * Push an action to the queue.
     *
     * @since 1.0.0
     *
     * @param int                  $timestamp Unix timestamp for when to run.
     * @param string               $hook Hook name to trigger.
     * @param array<string, mixed> $args Arguments to pass to hook.
     * @param string               $group Action group identifier.
     *
     * @return string Action ID on success, empty string on failure.
     */
    protected function push($timestamp, $hook, $args, $group): string
    {
        if ($this->is_scheduled($hook, $args, $group)) {
            return '';
        }
        $action = array('id' => $this->generate_action_id($hook, $args), 'hook' => sanitize_key($hook), 'args' => $args, 'group' => sanitize_key($group), 'schedule' => absint($timestamp), 'attempts' => 0, 'max_attempts' => $this->max_attempts, 'created' => time());
        $data = $this->get_queue_data();
        $data['actions'][] = $action;
        $this->update_queue_data($data);
        return $action['id'];
    }
    /**
     * Dispatch the async request.
     *
     * @since 1.0.0
     * @return void
     */
    protected function dispatch(): void
    {
        $url = add_query_arg(array('action' => $this->identifier, 'nonce' => wp_create_nonce($this->identifier)), admin_url('admin-ajax.php'));
        /**
         * Filters the AJAX URL for queue processing.
         *
         * @since 1.0.0
         *
         * @param string $url The AJAX URL.
         */
        $url = apply_filters($this->hook_prefix . '_queue_query_url', $url);
        $args = array(
            'timeout' => 5,
            'blocking' => false,
            'body' => array(),
            'cookies' => isset($_COOKIE) ? wp_unslash($_COOKIE) : array(),
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cookies are forwarded as-is to authenticate the loopback request.
            'sslverify' => apply_filters('https_local_ssl_verify', false),
        );
        /**
         * Filters the wp_remote_post arguments for queue dispatch.
         *
         * @since 1.0.0
         *
         * @param array $args Arguments passed to wp_remote_post.
         */
        $args = apply_filters($this->hook_prefix . '_queue_post_args', $args);
        wp_remote_post(esc_url_raw($url), $args);
    }
    /**
     * Process the queue.
     *
     * @since 1.0.0
     * @return void
     */
    public function handle(): void
    {
        $this->lock_process();
        do {
            $action = $this->get_next_action();
            if (!$action) {
                break;
            }
            $this->process_action($action);
            if ($this->time_exceeded() || $this->memory_exceeded()) {
                break;
            }
        } while (!$this->is_queue_empty());
        $this->unlock_process();
        if (!$this->is_queue_empty()) {
            $this->dispatch();
        } else {
            /**
             * Fires when the queue processing is completed.
             *
             * @since 1.0.0
             */
            do_action($this->hook_prefix . '_queue_completed');
        }
    }
    /**
     * Process an action.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $action Action data.
     *
     * @return void
     */
    protected function process_action($action): void
    {
        try {
            if (!has_action($action['hook'])) {
                $this->remove_action($action['id']);
                return;
            }
            $this->remove_action($action['id']);
            /**
             * Fires when processing a queued action.
             *
             * @since 1.0.0
             *
             * @param mixed ...$args Action arguments.
             */
            do_action_ref_array($action['hook'], array_values($action['args']));
            /**
             * Fires when an action completes successfully.
             *
             * @since 1.0.0
             *
             * @param string $id   Action ID.
             * @param string $hook Action hook name.
             * @param array  $args Action arguments.
             */
            do_action($this->hook_prefix . '_action_complete', $action['id'], $action['hook'], $action['args']);
        } catch (\Throwable $e) {
            ++$action['attempts'];
            if ($action['attempts'] >= $action['max_attempts']) {
                /**
                 * Fires when an action fails after maximum retry attempts.
                 *
                 * @since 1.0.0
                 *
                 * @param string $id      Action ID.
                 * @param string $hook    Action hook name.
                 * @param array  $args    Action arguments.
                 * @param string $message Error message.
                 */
                do_action($this->hook_prefix . '_action_failed', $action['id'], $action['hook'], $action['args'], $e->getMessage());
            } else {
                $data = $this->get_queue_data();
                $data['actions'][] = $action;
                $this->update_queue_data($data);
            }
        }
    }
    /**
     * Get the next action.
     *
     * @since 1.0.0
     * @return array<string, mixed>|null Action data or null if none available.
     */
    protected function get_next_action(): ?array
    {
        $data = $this->get_queue_data();
        $actions = $data['actions'] ?? array();
        if (empty($actions)) {
            return null;
        }
        usort($actions, function ($a, $b) {
            if ($a['schedule'] === $b['schedule']) {
                return $a['created'] - $b['created'];
            }
            return $a['schedule'] - $b['schedule'];
        });
        foreach ($actions as $action) {
            if ($action['schedule'] <= time()) {
                return $action;
            }
        }
        return null;
    }
    /**
     * Remove an action.
     *
     * @since 1.0.0
     *
     * @param string $action_id Action ID.
     *
     * @return void
     */
    protected function remove_action($action_id): void
    {
        $data = $this->get_queue_data();
        $actions = $data['actions'] ?? array();
        $actions = array_filter($actions, function ($action) use ($action_id) {
            return $action['id'] !== $action_id;
        });
        $data['actions'] = array_values($actions);
        $this->update_queue_data($data);
    }
    /**
     * Whether an action matches.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $action Action data.
     * @param string               $hook Hook name.
     * @param array<string, mixed> $args Arguments to match.
     * @param string               $group Group to match.
     *
     * @return bool True if matches, false otherwise.
     */
    protected function action_matches($action, $hook, $args, $group): bool
    {
        if ($action['hook'] !== $hook) {
            return false;
        }
        if (!empty($args) && $action['args'] !== $args) {
            return false;
        }
        if (!empty($group) && $action['group'] !== $group) {
            return false;
        }
        return true;
    }
    /**
     * Generate an action ID.
     *
     * @since 1.0.0
     *
     * @param string               $hook Hook name.
     * @param array<string, mixed> $args Arguments.
     *
     * @return string Action ID.
     */
    protected function generate_action_id($hook, $args): string
    {
        return md5($hook . wp_json_encode($args) . microtime());
    }
    /**
     * Whether the queue is empty.
     *
     * @since 1.0.0
     * @return bool True if empty, false otherwise.
     */
    protected function is_queue_empty(): bool
    {
        $data = $this->get_queue_data();
        $actions = $data['actions'] ?? array();
        if (empty($actions)) {
            return true;
        }
        foreach ($actions as $action) {
            if ($action['schedule'] <= time()) {
                return false;
            }
        }
        return true;
    }
    /**
     * Whether the queue is processing.
     *
     * @since 1.0.0
     * @return bool True if processing, false otherwise.
     */
    protected function is_processing(): bool
    {
        if (get_site_transient($this->identifier . '_process_lock')) {
            return true;
        }
        return false;
    }
    /**
     * Lock the queue.
     *
     * @since 1.0.0
     * @return void
     */
    protected function lock_process(): void
    {
        $this->start_time = time();
        /**
         * Filters the queue process lock duration in seconds.
         *
         * @since 1.0.0
         *
         * @param int $duration Lock duration in seconds. Default 60.
         */
        $lock_duration = apply_filters($this->hook_prefix . '_queue_lock_time', 60);
        set_site_transient($this->identifier . '_process_lock', microtime(), $lock_duration);
        /**
         * Fires when the queue processing is locked.
         *
         * @since 1.0.0
         */
        do_action($this->hook_prefix . '_queue_process_locked');
    }
    /**
     * Unlock the queue.
     *
     * @since 1.0.0
     * @return void
     */
    protected function unlock_process(): void
    {
        delete_site_transient($this->identifier . '_process_lock');
        /**
         * Fires when the queue processing is unlocked.
         *
         * @since 1.0.0
         */
        do_action($this->hook_prefix . '_queue_process_unlocked');
    }
    /**
     * Whether the time limit is exceeded.
     *
     * @since 1.0.0
     * @return bool True if exceeded, false otherwise.
     */
    protected function time_exceeded(): bool
    {
        /**
         * Filters the queue processing time limit in seconds.
         *
         * @since 1.0.0
         *
         * @param int $limit Time limit in seconds. Default 20.
         */
        $finish = $this->start_time + apply_filters($this->hook_prefix . '_queue_default_time_limit', $this->time_limit);
        $return = time() >= $finish;
        /**
         * Filters whether the time limit has been exceeded.
         *
         * @since 1.0.0
         *
         * @param bool $exceeded Whether the time limit has been exceeded.
         */
        return apply_filters($this->hook_prefix . '_queue_time_exceeded', $return);
    }
    /**
     * Whether the memory limit is exceeded.
     *
     * @since 1.0.0
     * @return bool True if exceeded, false otherwise.
     */
    protected function memory_exceeded(): bool
    {
        $memory_limit = $this->get_memory_limit() * 0.9;
        $current_memory = memory_get_usage(true);
        $return = $current_memory >= $memory_limit;
        /**
         * Filters whether the memory limit has been exceeded.
         *
         * @since 1.0.0
         *
         * @param bool $exceeded Whether the memory limit has been exceeded.
         */
        return apply_filters($this->hook_prefix . '_queue_memory_exceeded', $return);
    }
    /**
     * Get the memory limit.
     *
     * @since 1.0.0
     * @return int Memory limit in bytes.
     */
    protected function get_memory_limit(): int
    {
        if (function_exists('ini_get')) {
            $memory_limit = ini_get('memory_limit');
        } else {
            $memory_limit = '128M';
        }
        if (!$memory_limit || -1 === intval($memory_limit)) {
            $memory_limit = '128M';
        }
        return wp_convert_hr_to_bytes($memory_limit);
    }
    /**
     * Get the queue data.
     *
     * @since 1.0.0
     * @return array<string, mixed> Queue data.
     */
    protected function get_queue_data(): array
    {
        $data = get_option($this->identifier, array('actions' => array(), 'lock' => 0));
        if (!is_array($data) || !isset($data['actions'])) {
            $data = array('actions' => array());
        }
        return $data;
    }
    /**
     * Update the queue data.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed> $data Queue data.
     *
     * @return void
     */
    protected function update_queue_data($data): void
    {
        update_option($this->identifier, $data, false);
        if (empty($data['actions']) && wp_next_scheduled($this->identifier . '_cron')) {
            wp_clear_scheduled_hook($this->identifier . '_cron');
        }
    }
}