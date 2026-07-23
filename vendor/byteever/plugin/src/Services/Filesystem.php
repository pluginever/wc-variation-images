<?php

namespace PluginEver\VariationImages\B8\Services;

defined('ABSPATH') || exit;
/**
 * Handles filesystem access.
 *
 * Wraps WP_Filesystem with a clean API for file operations,
 * including path sanitization and stream downloads.
 *
 * @since 1.0.0
 * @package \B8
 */
class Filesystem
{
    /**
     * The WordPress filesystem instance.
     *
     * @since 1.0.0
     * @var \WP_Filesystem_Base
     */
    protected $fs;
    /**
     * Whether the filesystem uses direct file access.
     *
     * @since 1.0.0
     * @var bool
     */
    protected $direct;
    /**
     * Constructor.
     *
     * @since 1.0.0
     * @throws \Exception If the filesystem fails to initialize.
     */
    public function __construct()
    {
        global $wp_filesystem;
        if (!$wp_filesystem instanceof \WP_Filesystem_Base) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            if (false === WP_Filesystem()) {
                throw new \Exception('The WordPress filesystem could not be initialized.');
            }
        }
        $this->fs = $wp_filesystem;
        $this->direct = $this->fs instanceof \WP_Filesystem_Direct;
    }
    /**
     * Get the WordPress filesystem instance.
     *
     * @since 1.0.0
     * @return \WP_Filesystem_Base WordPress filesystem instance.
     */
    public function wp(): \WP_Filesystem_Base
    {
        return $this->fs;
    }
    /**
     * Whether a file exists.
     *
     * @since 1.0.0
     * @param string $path The file path to check.
     * @return bool True if the file exists, false otherwise.
     */
    public function exists(string $path): bool
    {
        $path = $this->sanitize_path($path);
        if ($this->direct) {
            return file_exists($path);
        }
        return $this->fs->exists($path);
    }
    /**
     * Get the file contents.
     *
     * @since 1.0.0
     * @param string $path The file path to read.
     * @return string|false The file contents on success, false on failure.
     */
    public function get(string $path)
    {
        $path = $this->sanitize_path($path);
        return $this->fs->get_contents($path);
    }
    /**
     * Write contents to a file.
     *
     * @since 1.0.0
     * @param string $path     The file path to write to.
     * @param string $contents The contents to write.
     * @return bool True on success, false on failure.
     */
    public function put(string $path, string $contents): bool
    {
        $path = $this->sanitize_path($path);
        return $this->fs->put_contents($path, $contents);
    }
    /**
     * Delete a file.
     *
     * @since 1.0.0
     * @param string $path The file path to delete.
     * @return bool True on success, false on failure.
     */
    public function delete(string $path): bool
    {
        $path = $this->sanitize_path($path);
        return $this->fs->delete($path);
    }
    /**
     * Get the file size.
     *
     * @since 1.0.0
     * @param string $path The file path.
     * @return int|false The file size in bytes on success, false on failure.
     */
    public function size(string $path)
    {
        $path = $this->sanitize_path($path);
        return $this->fs->size($path);
    }
    /**
     * Get the file modified time.
     *
     * @since 1.0.0
     * @param string $path The file path.
     * @return int|false Unix timestamp on success, false on failure.
     */
    public function mtime(string $path)
    {
        $path = $this->sanitize_path($path);
        return $this->fs->mtime($path);
    }
    /**
     * Get the file lines.
     *
     * @since 1.0.0
     * @param string $path The file path.
     * @return array<int, string>|false Array of lines on success, false on failure.
     */
    public function lines(string $path)
    {
        $path = $this->sanitize_path($path);
        if ($this->direct) {
            return file($path);
        }
        return $this->fs->get_contents_array($path);
    }
    /**
     * Create a directory.
     *
     * @since 1.0.0
     * @param string $path The directory path.
     * @param int    $mode Optional. Directory permissions. Default 0755.
     * @return bool True on success, false on failure.
     */
    public function mkdir(string $path, int $mode = 0755): bool
    {
        return $this->fs->mkdir($path, $mode);
    }
    /**
     * Remove a directory.
     *
     * @since 1.0.0
     * @param string $path      The directory path.
     * @param bool   $recursive Whether to remove contents recursively.
     * @return bool True on success, false on failure.
     */
    public function rmdir(string $path, bool $recursive = false): bool
    {
        return $this->fs->rmdir($path, $recursive);
    }
    /**
     * Whether direct file access is used.
     *
     * @since 1.0.0
     * @return bool True if using direct filesystem access.
     */
    public function is_direct(): bool
    {
        return $this->direct;
    }
    /**
     * Stream a file download.
     *
     * @since 1.0.0
     * @param string      $file     Path to the file.
     * @param string|null $filename Optional. The download filename.
     * @return bool|\WP_Error True on success, WP_Error on failure.
     */
    public function download(string $file, $filename = null)
    {
        if (!$this->exists($file)) {
            return new \WP_Error('file_not_found', 'File not found.');
        }
        $real_path = realpath($file);
        $upload_dir = wp_upload_dir();
        $upload_path = realpath($upload_dir['basedir']);
        if (!$real_path || !$upload_path || !str_starts_with($real_path, $upload_path)) {
            return new \WP_Error('access_denied', 'Access denied.');
        }
        $filename = sanitize_file_name($filename ?? basename($file));
        $file_info = wp_check_filetype($file);
        $mime_type = !empty($file_info['type']) ? $file_info['type'] : 'application/octet-stream';
        $file_size = $this->size($file);
        if (function_exists('gc_enable')) {
            gc_enable();
        }
        // phpcs:disable WordPress.PHP.IniSet.Risky, WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv, Squiz.PHP.DiscouragedFunctions.Discouraged -- Required for file streaming.
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', 'Off');
        @ini_set('output_buffering', 'Off');
        @ini_set('output_handler', '');
        ignore_user_abort(true);
        set_time_limit(0);
        // phpcs:enable WordPress.PHP.IniSet.Risky, WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv, Squiz.PHP.DiscouragedFunctions.Discouraged
        nocache_headers();
        header('Content-Type: ' . $mime_type . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $file_size);
        readfile($real_path);
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Direct read of the validated path required to stream without buffering in memory.
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        return true;
    }
    /**
     * Protect a directory from direct access.
     *
     * Creates .htaccess (deny from all) and index.php (silence) files
     * to prevent directory listing and direct file access via the browser.
     *
     * @since 1.0.0
     * @param string $path      The directory path to protect.
     * @param bool   $recursive Whether to also protect subdirectories.
     * @return void
     */
    public function protect(string $path, bool $recursive = false): void
    {
        if (!$this->fs->is_dir($path)) {
            $this->mkdir($path);
        }
        $htaccess = $path . '/.htaccess';
        if (!$this->exists($htaccess)) {
            $this->put($htaccess, "deny from all\n");
        }
        $index = $path . '/index.php';
        if (!$this->exists($index)) {
            $this->put($index, "<?php\n// Silence is golden.\n");
        }
        if ($recursive) {
            $contents = $this->fs->dirlist($path, false, false);
            if (is_array($contents)) {
                foreach ($contents as $name => $info) {
                    if ('d' === $info['type']) {
                        $this->protect($path . '/' . $name, true);
                    }
                }
            }
        }
    }
    /**
     * Sanitize a file path.
     *
     * @since 1.0.0
     * @param string $path The file path to sanitize.
     * @return string The sanitized file path.
     */
    protected function sanitize_path(string $path): string
    {
        if (!str_contains($path, '://') && !str_contains($path, rawurlencode('://'))) {
            return $path;
        }
        $protocols = array('phar://', 'php://', 'glob://', 'data://', 'expect://', 'zip://', 'rar://', 'zlib://');
        $protocols = array_merge($protocols, array_map('urlencode', $protocols));
        foreach ($protocols as $protocol) {
            $pattern = '#^' . preg_quote($protocol, '#') . '#i';
            $path = preg_replace($pattern, '', $path);
        }
        return $path;
    }
}