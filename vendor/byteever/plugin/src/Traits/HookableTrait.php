<?php

namespace PluginEver\VariationImages\B8\Traits;

defined('ABSPATH') || exit;
/**
 * Hookable trait.
 *
 * Wraps the action and filter functions so hook names are automatically
 * prefixed per plugin, keeping custom hooks namespaced and consistent.
 *
 * @since 1.0.0
 * @package \B8
 */
trait HookableTrait
{
    /**
     * Get the prefixed hook name.
     *
     * @since 1.0.0
     * @param string $name The hook name (without prefix).
     * @return string The prefixed hook name.
     */
    public function hook_name(string $name): string
    {
        $sep = $this->hook_separator;
        $hook = $this->hook_prefix . $sep . $name;
        $hook = preg_replace('/[^A-Za-z0-9]/', $sep, $hook);
        $hook = preg_replace('/[' . preg_quote($sep, '/') . ']+/', $sep, $hook);
        return strtolower(trim($hook, $sep));
    }
    /**
     * Fire a prefixed action.
     *
     * @since 1.0.0
     * @param string $hook The hook name (without prefix).
     * @param mixed  ...$args Arguments to pass to the hook.
     * @return void
     */
    public function do_action(string $hook, ...$args): void
    {
        do_action($this->hook_name($hook), ...$args);
    }
    /**
     * Add an action.
     *
     * @since 1.0.0
     * @param string $hook The hook name.
     * @param mixed  $callback The callback to execute.
     * @param int    $priority Optional. Hook priority. Default 10.
     * @param int    $accepted_args Optional. Number of arguments. Default 1.
     * @return bool True on success, false on failure.
     */
    public function add_action($hook, $callback, $priority = 10, $accepted_args = 1): bool
    {
        if (is_callable($callback) && !is_string($callback)) {
            return add_action($hook, $callback, $priority, $accepted_args);
        }
        return add_action($hook, $this->callback($callback), $priority, $accepted_args);
    }
    /**
     * Add a prefixed action.
     *
     * @since 1.0.0
     * @param string $hook          The hook name (without prefix).
     * @param mixed  $callback      The callback to execute.
     * @param int    $priority      Optional. Hook priority. Default 10.
     * @param int    $accepted_args Optional. Number of arguments. Default 1.
     * @return bool True on success, false on failure.
     */
    public function on_action(string $hook, $callback, int $priority = 10, int $accepted_args = 1): bool
    {
        return $this->add_action($this->hook_name($hook), $callback, $priority, $accepted_args);
    }
    /**
     * Remove an action.
     *
     * @since 1.0.0
     * @param string $hook The hook name.
     * @param mixed  $callback The callback to remove.
     * @param int    $priority Optional. Hook priority. Default 10.
     * @return bool True on success, false on failure.
     */
    public function remove_action($hook, $callback, $priority = 10): bool
    {
        if (is_callable($callback) && !is_string($callback)) {
            return remove_action($hook, $callback, $priority);
        }
        return remove_action($hook, $this->callback($callback), $priority);
    }
    /**
     * Apply a prefixed filter.
     *
     * @since 1.0.0
     * @param string $hook  The hook name (without prefix).
     * @param mixed  $value The value to filter.
     * @param mixed  ...$args Additional arguments to pass to the hook.
     * @return mixed The filtered value.
     */
    public function apply_filters(string $hook, $value, ...$args)
    {
        return apply_filters($this->hook_name($hook), $value, ...$args);
    }
    /**
     * Add a filter.
     *
     * @since 1.0.0
     * @param string $hook The hook name.
     * @param mixed  $callback The callback to execute.
     * @param int    $priority Optional. Filter priority. Default 10.
     * @param int    $accepted_args Optional. Number of arguments. Default 1.
     * @return bool True on success, false on failure.
     */
    public function add_filter($hook, $callback, $priority = 10, $accepted_args = 1): bool
    {
        if (is_callable($callback) && !is_string($callback)) {
            return add_filter($hook, $callback, $priority, $accepted_args);
        }
        return add_filter($hook, $this->callback($callback), $priority, $accepted_args);
    }
    /**
     * Add a prefixed filter.
     *
     * @since 1.0.0
     * @param string $hook          The hook name (without prefix).
     * @param mixed  $callback      The callback to execute.
     * @param int    $priority      Optional. Hook priority. Default 10.
     * @param int    $accepted_args Optional. Number of arguments. Default 1.
     * @return bool True on success, false on failure.
     */
    public function on_filter(string $hook, $callback, int $priority = 10, int $accepted_args = 1): bool
    {
        return $this->add_filter($this->hook_name($hook), $callback, $priority, $accepted_args);
    }
    /**
     * Remove a filter.
     *
     * @since 1.0.0
     * @param string $hook The hook name.
     * @param mixed  $callback The callback to remove.
     * @param int    $priority Optional. Hook priority. Default 10.
     * @return bool True on success, false on failure.
     */
    public function remove_filter($hook, $callback, $priority = 10): bool
    {
        if (is_callable($callback) && !is_string($callback)) {
            return remove_filter($hook, $callback, $priority);
        }
        return remove_filter($hook, $this->callback($callback), $priority);
    }
    /**
     * Register the plugin activation hook.
     *
     * @since 1.0.0
     * @param mixed $callback The callback to run on activation.
     * @return void
     */
    public function on_activation($callback): void
    {
        if (is_callable($callback) && !is_string($callback)) {
            register_activation_hook($this->file, $callback);
            return;
        }
        register_activation_hook($this->file, $this->callback($callback));
    }
    /**
     * Register the plugin deactivation hook.
     *
     * @since 1.0.0
     * @param mixed $callback The callback to run on deactivation.
     * @return void
     */
    public function on_deactivation($callback): void
    {
        if (is_callable($callback) && !is_string($callback)) {
            register_deactivation_hook($this->file, $callback);
            return;
        }
        register_deactivation_hook($this->file, $this->callback($callback));
    }
}