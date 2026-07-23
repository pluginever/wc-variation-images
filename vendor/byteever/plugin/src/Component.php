<?php

namespace PluginEver\VariationImages\B8;

defined('ABSPATH') || exit;
/**
 * Base component class.
 *
 * Holds the application instance and provides an opt-in registration lifecycle:
 * override register() to wire hooks, autoload() to gate by context, and list
 * child components in $components.
 *
 * @since   1.0.0
 * @package \B8
 */
abstract class Component
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected App $app;
    /**
     * Child components.
     *
     * @since 1.0.0
     * @var array<int|string, class-string>
     */
    public array $components = array();
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
     * Whether to load.
     *
     * @since 1.0.0
     * @return bool True when the component should register.
     */
    public function autoload(): bool
    {
        return true;
    }
    /**
     * Register hooks.
     *
     * @since 1.0.0
     * @return void
     */
    public function register(): void
    {
        // Override to register the component's hooks.
    }
}