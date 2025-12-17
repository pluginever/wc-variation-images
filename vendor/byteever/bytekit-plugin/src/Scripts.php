<?php

namespace WooCommerceVariationImages\ByteKit;

use WooCommerceVariationImages\ByteKit\Interfaces\Scriptable;
defined('ABSPATH') || exit;
/**
 * Scripts handler class.
 *
 * @since   1.0.0
 * @version 1.0.2
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package \ByteKit/Plugin
 * @license GPL-3.0+
 */
class Scripts implements Scriptable
{
    /**
     * The plugin instance.
     *
     * @since 1.0.0
     * @var Plugin
     */
    protected $plugin;
    /**
     * The localized data.
     *
     * @since 1.0.0
     * @var array
     */
    protected $localized = array();
    /**
     * Construct and initialize the service trait.
     *
     * @param Plugin $plugin The plugin instance.
     *
     * @since 1.0.0
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }
    /**
     * Register a script.
     *
     * @param string $handle Name of the script. Should be unique.
     * @param string $src Relative path to the script from the plugin's assets directory.
     * @param array  $deps An array of registered script handles this script depends on. Default empty array.
     * @param bool   $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_script($handle, $src, $deps = array(), $in_footer = false)
    {
        // check if $src is relative or absolute.
        if (!preg_match('/^(http|https):\/\//', $src)) {
            $url = $this->plugin->get_assets_url($src);
            $path = $this->plugin->get_assets_path($src);
        } else {
            $url = $src;
            $path = str_replace($this->plugin->get_dir_url(), $this->plugin->get_dir_path(), $src);
        }
        $php_file = str_replace('.js', '.asset.php', $path);
        $asset = $php_file && file_exists($php_file) ? require $php_file : array('dependencies' => array(), 'version' => $this->plugin->get_version());
        $deps = array_merge($asset['dependencies'], $deps);
        $ver = $asset['version'];
        wp_register_script($handle, $url, $deps, $ver, $in_footer);
        if (in_array('wp-i18n', $deps, true)) {
            wp_set_script_translations($handle, $this->plugin->textdomain, $this->plugin->get_lang_path());
        }
    }
    /**
     * Register a style.
     *
     * @param string $handle Name of the stylesheet. Should be unique.
     * @param string $src Relative path to the stylesheet from the plugin's assets directory.
     * @param array  $deps An array of registered stylesheet handles this stylesheet depends on. Default empty array.
     * @param string $media The media for which this stylesheet has been defined. Default 'all'.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_style($handle, $src, $deps = array(), $media = 'all')
    {
        // check if $src is relative or absolute.
        if (!preg_match('/^(http|https):\/\//', $src)) {
            $url = $this->plugin->get_assets_url($src);
            $path = $this->plugin->get_assets_path($src);
        } else {
            $url = $src;
            $path = str_replace($this->plugin->get_dir_url(), $this->plugin->get_dir_path(), $src);
        }
        $php_file = str_replace('.css', '.asset.php', $path);
        $asset = $php_file && file_exists($php_file) ? require $php_file : array('version' => $this->plugin->get_version());
        $ver = $asset['version'];
        wp_register_style($handle, $url, $deps, $ver, $media);
        // check if rtl file exists.
        if (is_rtl()) {
            $rtl_file = str_replace('.css', '-rtl.css', $path);
            if (file_exists($rtl_file)) {
                wp_style_add_data($handle, 'rtl', 'replace');
            }
        }
    }
    /**
     * Enqueue scripts helper.
     *
     * @param string $handle Name of the script. Should be unique.
     * @param string $src Relative path to the script from the plugin's assets directory.
     * @param array  $deps An array of registered script handles this script depends on. Default empty array.
     * @param bool   $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_script($handle, $src = null, $deps = array(), $in_footer = false)
    {
        if (!wp_scripts()->query($handle) && !empty($src)) {
            $this->register_script($handle, $src, $deps, $in_footer);
        }
        // Check if the script is register using wp_scripts otherwise skip.
        if (wp_scripts()->query($handle)) {
            wp_enqueue_script($handle);
            if (isset($this->localized[$handle]) && is_array($this->localized[$handle])) {
                foreach ($this->localized[$handle] as $name => $data) {
                    wp_localize_script($handle, $name, $data);
                }
            }
        }
    }
    /**
     * Enqueue styles helper.
     *
     * @param string $handle Name of the stylesheet. Should be unique.
     * @param string $src Relative path to the stylesheet from the plugin's assets directory.
     * @param array  $deps An array of registered stylesheet handles this stylesheet depends on. Default empty array.
     * @param string $media The media for which this stylesheet has been defined. Default 'all'.
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_style($handle, $src = null, $deps = array(), $media = 'all')
    {
        // check if the style is already registered.
        if (!wp_styles()->query($handle) && !empty($src)) {
            $this->register_style($handle, $src, $deps, $media);
        }
        // Check if the style is register using wp_styles otherwise skip.
        if (wp_styles()->query($handle)) {
            wp_enqueue_style($handle);
        }
    }
    /**
     * Localize a script.
     *
     * @param string $handle Name of the script. Should be unique.
     * @param string $name The name of the variable which will contain the data.
     * @param array  $data The data to be localized.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_data($handle, $name, $data)
    {
        // if this called after the script is enqueued, log doing wrong.
        if (did_action('wp_enqueue_scripts')) {
            _doing_it_wrong(__METHOD__, esc_html('The script has already been enqueued. Please localize the script before enqueuing it.'), '1.0.0');
            return;
        }
        if (!isset($this->localized[$handle])) {
            $this->localized[$handle] = array();
        }
        $this->localized[$handle][$name] = $data;
    }
}