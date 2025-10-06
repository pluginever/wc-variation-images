<?php

namespace WooCommerceVariationImages\ByteKit\Interfaces;

defined('ABSPATH') || exit;
/**
 * Describes a class that can be used to register scripts.
 *
 * @since   1.0.0
 * @version 1.0.2
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package \WooCommerceVariationImages\ByteKit\Plugin
 * @license GPL-3.0+
 */
interface Scriptable
{
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
    public function register_script($handle, $src, $deps = array(), $in_footer = false);
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
    public function register_style($handle, $src, $deps = array(), $media = 'all');
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
    public function enqueue_script($handle, $src = null, $deps = array(), $in_footer = false);
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
    public function enqueue_style($handle, $src = null, $deps = array(), $media = 'all');
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
    public function add_data($handle, $name, $data);
}