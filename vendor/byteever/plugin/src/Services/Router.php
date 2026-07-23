<?php

namespace PluginEver\VariationImages\B8\Services;

use PluginEver\VariationImages\B8\App;
defined('ABSPATH') || exit;
/**
 * Handles routing.
 *
 * Provides route registration, grouping, and automatic WordPress
 * REST API integration with permission handling.
 *
 * @since 1.0.0
 * @package \B8
 */
class Router
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected App $app;
    /**
     * REST API namespace.
     *
     * @since 1.0.0
     * @var string
     */
    protected string $namespace;
    /**
     * Current route prefix.
     *
     * @since 1.0.0
     * @var string
     */
    protected string $prefix = '';
    /**
     * Current route permission.
     *
     * @since 1.0.0
     * @var string|callable|null
     */
    protected $permission = '__return_true';
    /**
     * Current controller class name.
     *
     * @since 1.0.0
     * @var string|null
     */
    protected ?string $controller = null;
    /**
     * Route group stack.
     *
     * @since 1.0.0
     * @var array<int, array<string, mixed>>
     */
    protected array $group_stack = array();
    /**
     * Constructor.
     *
     * @param App $app Application instance.
     *
     * @since 1.0.0
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->namespace = implode('/', array_filter(array($this->app->rest_prefix, $this->app->rest_version)));
    }
    /**
     * Register a GET route.
     *
     * @param string                               $route   Route pattern.
     * @param callable|string|array<string, mixed> $handler Route handler or configuration array.
     *
     * @since 1.0.0
     * @return self
     */
    public function get($route, $handler): self
    {
        return $this->add_route('GET', $route, $handler);
    }
    /**
     * Register a POST route.
     *
     * @param string                               $route   Route pattern.
     * @param callable|string|array<string, mixed> $handler Route handler or configuration array.
     *
     * @since 1.0.0
     * @return self
     */
    public function post($route, $handler): self
    {
        return $this->add_route('POST', $route, $handler);
    }
    /**
     * Register a PUT route.
     *
     * @param string                               $route   Route pattern.
     * @param callable|string|array<string, mixed> $handler Route handler or configuration array.
     *
     * @since 1.0.0
     * @return self
     */
    public function put($route, $handler): self
    {
        return $this->add_route('PUT', $route, $handler);
    }
    /**
     * Register a DELETE route.
     *
     * @param string                               $route   Route pattern.
     * @param callable|string|array<string, mixed> $handler Route handler or configuration array.
     *
     * @since 1.0.0
     * @return self
     */
    public function delete($route, $handler): self
    {
        return $this->add_route('DELETE', $route, $handler);
    }
    /**
     * Register a PATCH route.
     *
     * @param string                               $route   Route pattern.
     * @param callable|string|array<string, mixed> $handler Route handler or configuration array.
     *
     * @since 1.0.0
     * @return self
     */
    public function patch($route, $handler): self
    {
        return $this->add_route('PATCH', $route, $handler);
    }
    /**
     * Register a resource route.
     *
     * Creates standard resource routes following WordPress REST API conventions.
     * Only registers routes for methods that exist on the controller.
     *
     * | Method      | URI              | Handler      |
     * |-------------|------------------|--------------|
     * | GET         | /name            | get_items    |
     * | POST        | /name            | create_item  |
     * | GET         | /name/{id}       | get_item     |
     * | PUT, PATCH  | /name/{id}       | update_item  |
     * | DELETE      | /name/{id}       | delete_item  |
     * | GET         | /name/options    | get_options  |
     * | POST        | /name/batch      | batch_items  |
     * | GET, POST   | /name/import     | import_items |
     * | GET         | /name/export     | export_items |
     *
     * @param string               $name       Resource name used as route prefix.
     * @param string               $controller Controller class name.
     * @param array<string, mixed> $options {
     *     Optional. Resource configuration options.
     *
     *     @type array  $only       Actions to include. Default all.
     *     @type array  $except     Actions to exclude. Default none.
     *     @type string $parameter  Route parameter. Accepts 'id', 'slug', or full regex pattern.
     *                              Default '(?P<id>[\d]+)'.
     *     @type string $permission Permission capability for all routes.
     * }
     *
     * @since 1.0.0
     * @return self
     */
    public function resource(string $name, string $controller, array $options = array()): self
    {
        $defaults = array('only' => array(), 'except' => array(), 'parameter' => '(?P<id>[\d]+)', 'permission' => null);
        $options = array_merge($defaults, $options);
        // Handle parameter shortcuts.
        if ('id' === $options['parameter']) {
            $options['parameter'] = '(?P<id>[\d]+)';
        } elseif ('slug' === $options['parameter']) {
            $options['parameter'] = '(?P<slug>[a-z0-9-]+)';
        }
        // Define resource actions: action => [ method(s), route, handler ].
        $actions = array('get_items' => array('GET', '/', 'get_items'), 'create_item' => array('POST', '/', 'create_item'), 'get_item' => array('GET', $options['parameter'], 'get_item'), 'update_item' => array(array('PUT', 'PATCH'), $options['parameter'], 'update_item'), 'delete_item' => array('DELETE', $options['parameter'], 'delete_item'), 'get_options' => array('GET', '/options', 'get_options'), 'batch_items' => array('POST', '/batch', 'batch_items'), 'import_items' => array(array('GET', 'POST'), '/import', 'import_items'), 'export_items' => array('GET', '/export', 'export_items'));
        // Filter actions by only/except.
        if (!empty($options['only'])) {
            $actions = array_intersect_key($actions, array_flip((array) $options['only']));
        } elseif (!empty($options['except'])) {
            $actions = array_diff_key($actions, array_flip((array) $options['except']));
        }
        // Build group attributes.
        $attributes = array('prefix' => $name, 'controller' => $controller);
        if (!empty($options['permission'])) {
            $attributes['permission'] = $options['permission'];
        }
        // Register routes within a group.
        $this->group($attributes, function (Router $router) use ($actions) {
            foreach ($actions as $action) {
                $methods = (array) $action[0];
                $route = $action[1];
                $handler = $action[2];
                foreach ($methods as $method) {
                    $router->add_route($method, $route, $handler);
                }
            }
        });
        return $this;
    }
    /**
     * Create a route group.
     *
     * @param array<string, mixed>|string $attributes Group attributes (prefix, permission, controller).
     * @param callable                    $callback   Callback to define routes.
     *
     * @since 1.0.0
     * @return self
     */
    public function group($attributes, $callback): self
    {
        if (is_string($attributes)) {
            $attributes = array('prefix' => $attributes);
        }
        $this->group_stack[] = array('prefix' => $this->prefix, 'permission' => $this->permission, 'controller' => $this->controller);
        if (isset($attributes['prefix'])) {
            $this->prefix = empty($this->prefix) ? $attributes['prefix'] : rtrim($this->prefix, '/') . '/' . ltrim($attributes['prefix'], '/');
        }
        if (isset($attributes['permission'])) {
            $this->permission = $attributes['permission'];
        }
        if (isset($attributes['controller']) && is_string($attributes['controller'])) {
            $this->controller = $attributes['controller'];
        }
        try {
            call_user_func($callback, $this);
        } finally {
            $previous = array_pop($this->group_stack);
            $this->prefix = $previous['prefix'] ?? '';
            $this->permission = $previous['permission'] ?? '__return_true';
            $this->controller = $previous['controller'] ?? null;
        }
        return $this;
    }
    /**
     * Make an internal REST request.
     *
     * @since 1.0.0
     *
     * @param string               $endpoint REST API endpoint path.
     * @param array<string, mixed> $params   Request parameters.
     * @param string               $method   HTTP method (GET, POST, PUT, PATCH, DELETE).
     *
     * @return mixed Response data.
     */
    public function request($endpoint, $params = array(), $method = 'GET')
    {
        $request = new \WP_REST_Request($method, $endpoint);
        if ($params && 'GET' === $method) {
            $request->set_query_params($params);
        } elseif ($params && in_array($method, array('POST', 'PUT', 'PATCH', 'DELETE'), true)) {
            $request->set_body_params($params);
        }
        $response = rest_do_request($request);
        $server = rest_get_server();
        $json = wp_json_encode($server->response_to_data($response, false));
        return json_decode($json, true);
    }
    /**
     * Register a route.
     *
     * @param string          $method  HTTP method.
     * @param string          $route   Route pattern.
     * @param callable|string $handler Route handler (callable or controller method name).
     *
     * @since 1.0.0
     * @return self
     */
    protected function add_route($method, $route, $handler): self
    {
        $pattern = rtrim(($this->prefix ? rtrim($this->prefix, '/') . '/' : '') . ltrim($route, '/'), '/');
        $controller_class = null;
        if (is_array($handler) && 2 === count($handler) && is_string($handler[0])) {
            $controller_class = $handler[0];
        } elseif (is_string($handler) && $this->controller) {
            $controller_class = $this->controller;
        }
        $args = array('methods' => $method, 'callback' => $handler, 'permission_callback' => $this->permission, 'args' => array());
        if ($controller_class) {
            $controller = $this->app->make($controller_class);
            $method_name = is_array($handler) ? $handler[1] : $handler;
            // Skip registration if the controller method does not exist.
            if (!method_exists($controller, $method_name)) {
                return $this;
            }
            $args['callback'] = array($controller, $method_name);
            $is_collection = '' === trim($route, '/');
            if ($is_collection && 'GET' === $method && method_exists($controller, 'get_collection_params')) {
                $args['args'] = $controller->get_collection_params();
            } elseif ($is_collection && 'POST' === $method && method_exists($controller, 'get_endpoint_args_for_item_schema')) {
                $args['args'] = $controller->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE);
            } elseif ('GET' === $method && method_exists($controller, 'get_context_param')) {
                $args['args']['context'] = $controller->get_context_param(array('default' => 'view'));
            } elseif (in_array($method, array('PUT', 'PATCH'), true) && method_exists($controller, 'get_endpoint_args_for_item_schema')) {
                $args['args'] = $controller->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE);
            }
        }
        $permission = $args['permission_callback'];
        if (is_string($permission) && !is_callable($permission)) {
            $args['permission_callback'] = function () use ($permission) {
                return current_user_can($permission);
            };
        }
        register_rest_route($this->namespace, $pattern, $args);
        return $this;
    }
}