<?php

namespace PluginEver\VariationImages\B8\Services;

use PluginEver\VariationImages\B8\App;
defined('ABSPATH') || exit;
/**
 * Handles logging.
 *
 * Provides PSR-3 style logging to a plugin-specific file with level
 * filtering, message interpolation, and automatic log rotation.
 *
 * @since 1.0.0
 * @package \B8
 */
class Logger
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected $app;
    /**
     * Logger name/channel.
     *
     * @since 1.0.0
     * @var string
     */
    protected $name;
    /**
     * Log levels with numeric values.
     *
     * @since 1.0.0
     * @var array<string, int>
     */
    protected $levels = array('emergency' => 0, 'alert' => 1, 'critical' => 2, 'error' => 3, 'warning' => 4, 'notice' => 5, 'info' => 6, 'debug' => 7);
    /**
     * Minimum log level.
     *
     * @since 1.0.0
     * @var string
     */
    protected string $log_level;
    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param App         $app  Application instance.
     * @param string|null $name Logger name/channel. Defaults to plugin slug.
     */
    public function __construct(App $app, ?string $name = null)
    {
        $this->app = $app;
        $this->name = $name ?? $this->app->slug;
        $this->log_level = $this->app->log_level;
    }
    /**
     * Log an emergency message.
     *
     * @param string               $message The log message.
     * @param array<string, mixed> $context Additional context data.
     *
     * @since 1.0.0
     * @return void
     */
    public function emergency($message, $context = array()): void
    {
        $this->log('emergency', $message, $context);
    }
    /**
     * Log an alert message.
     *
     * @param string               $message The log message.
     * @param array<string, mixed> $context Additional context data.
     *
     * @since 1.0.0
     * @return void
     */
    public function alert($message, $context = array()): void
    {
        $this->log('alert', $message, $context);
    }
    /**
     * Log a critical message.
     *
     * @param string               $message The log message.
     * @param array<string, mixed> $context Additional context data.
     *
     * @since 1.0.0
     * @return void
     */
    public function critical($message, $context = array()): void
    {
        $this->log('critical', $message, $context);
    }
    /**
     * Log an error message.
     *
     * @param string               $message The log message.
     * @param array<string, mixed> $context Additional context data.
     *
     * @since 1.0.0
     * @return void
     */
    public function error($message, $context = array()): void
    {
        $this->log('error', $message, $context);
    }
    /**
     * Log a warning message.
     *
     * @param string               $message The log message.
     * @param array<string, mixed> $context Additional context data.
     *
     * @since 1.0.0
     * @return void
     */
    public function warning($message, $context = array()): void
    {
        $this->log('warning', $message, $context);
    }
    /**
     * Log a notice message.
     *
     * @param string               $message The log message.
     * @param array<string, mixed> $context Additional context data.
     *
     * @since 1.0.0
     * @return void
     */
    public function notice($message, $context = array()): void
    {
        $this->log('notice', $message, $context);
    }
    /**
     * Log an info message.
     *
     * @param string               $message The log message.
     * @param array<string, mixed> $context Additional context data.
     *
     * @since 1.0.0
     * @return void
     */
    public function info($message, $context = array()): void
    {
        $this->log('info', $message, $context);
    }
    /**
     * Log a debug message.
     *
     * @param string               $message The log message.
     * @param array<string, mixed> $context Additional context data.
     *
     * @since 1.0.0
     * @return void
     */
    public function debug($message, $context = array()): void
    {
        $this->log('debug', $message, $context);
    }
    /**
     * Log a message.
     *
     * @param string               $level   The log level.
     * @param string               $message The log message.
     * @param array<string, mixed> $context Additional context data.
     *
     * @since 1.0.0
     * @return void
     */
    public function log($level, $message, $context = array()): void
    {
        if (!$this->should_log($level)) {
            return;
        }
        $formatted_message = $this->format_message($level, $message, $context);
        $this->write_log($formatted_message);
    }
    /**
     * Get the log file path.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_file(): string
    {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/' . $this->app->slug;
        if (!$this->app->fs->exists($log_dir)) {
            $this->app->fs->protect($log_dir);
        }
        $auth_key = defined('AUTH_KEY') ? AUTH_KEY : 'default-key';
        $hash = substr(md5($auth_key), 0, 8);
        $filename = sprintf('%s-%s.log', $this->name, $hash);
        return $log_dir . '/' . $filename;
    }
    /**
     * Remove the log files.
     *
     * @since 1.0.0
     * @return bool
     */
    public function cleanup(): bool
    {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/' . $this->app->slug;
        if (!$this->app->fs->exists($log_dir)) {
            return true;
        }
        return $this->app->fs->rmdir($log_dir, true);
    }
    /**
     * Whether a message should be logged.
     *
     * @param string $level The log level.
     *
     * @since 1.0.0
     * @return bool
     */
    protected function should_log($level): bool
    {
        if (!isset($this->levels[$level])) {
            return false;
        }
        if (!isset($this->levels[$this->log_level])) {
            return false;
        }
        return $this->levels[$level] <= $this->levels[$this->log_level];
    }
    /**
     * Format a log message.
     *
     * @param string               $level   The log level.
     * @param string               $message The log message.
     * @param array<string, mixed> $context Additional context data.
     *
     * @since 1.0.0
     * @return string
     */
    protected function format_message($level, $message, $context): string
    {
        $timestamp = current_time('Y-m-d H:i:s');
        $level = strtoupper($level);
        $message = $this->interpolate($message, $context);
        $formatted = "[{$timestamp}] {$level}: {$message}";
        if (!empty($context)) {
            $context_str = wp_json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $formatted .= " {$context_str}";
        }
        return $formatted;
    }
    /**
     * Interpolate the message placeholders.
     *
     * @param string               $message The message with placeholders.
     * @param array<string, mixed> $context Context values.
     *
     * @since 1.0.0
     * @return string
     */
    protected function interpolate($message, $context): string
    {
        $replace = array();
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }
    /**
     * Rotate the log file.
     *
     * @since 1.0.0
     * @return void
     */
    protected function log_rotate(): void
    {
        $log_file = $this->get_file();
        $max_size = $this->app->log_max_size;
        if (!$this->app->fs->exists($log_file)) {
            return;
        }
        $size = $this->app->fs->size($log_file);
        if (false !== $size && $size >= $max_size) {
            $this->app->fs->wp()->move($log_file, $log_file . '.1', true);
        }
    }
    /**
     * Write to the log file.
     *
     * @param string $message The formatted message.
     *
     * @since 1.0.0
     * @return void
     */
    protected function write_log($message): void
    {
        $this->log_rotate();
        $log_file = $this->get_file();
        $existing = $this->app->fs->exists($log_file) ? $this->app->fs->get($log_file) : '';
        $this->app->fs->put($log_file, $existing . $message . PHP_EOL);
    }
}