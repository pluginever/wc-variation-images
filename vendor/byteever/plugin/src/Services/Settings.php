<?php

namespace PluginEver\VariationImages\B8\Services;

use PluginEver\VariationImages\B8\App;
defined('ABSPATH') || exit;
/**
 * Handles plugin settings.
 *
 * Holds the field definitions and exposes them, grouped, to the admin UI and
 * REST, plus value read and write. It has no hooks and renders nothing.
 *
 * @since   1.0.0
 * @package \B8
 */
class Settings
{
    /**
     * Application instance.
     *
     * @since 1.0.0
     * @var App
     */
    protected App $app;
    /**
     * Resolved settings keyed by group.
     *
     * @since 1.0.0
     * @var array<string, array{group: string, title: string, fields: array<int, array<string, mixed>>}>|null
     */
    protected ?array $settings = null;
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
     * Get the settings.
     *
     * @since 1.0.0
     * @return array<string, array{group: string, title: string, fields: array<int, array<string, mixed>>}> Resolved settings keyed by group.
     */
    public function get_settings(): array
    {
        if (null === $this->settings) {
            $this->settings = array();
            /**
             * Filters the settings definition.
             *
             * @since 1.0.0
             * @param array<string, mixed> $settings Settings definition keyed by group.
             */
            $settings = (array) $this->app->apply_filters('settings', $this->define_settings());
            /**
             * Filters the settings groups.
             *
             * @since 1.0.0
             * @param array<string, string|null> $groups Group titles keyed by id; null when untitled.
             */
            $groups = (array) $this->app->apply_filters('settings_groups', array_map(static fn($group) => is_array($group) ? $group['title'] ?? null : null, $settings));
            foreach ($groups as $group => $title) {
                if (is_int($group)) {
                    $group = $title;
                    $title = null;
                }
                $fields = $settings[$group] ?? array();
                $group = sanitize_key((string) $group);
                if (empty($group)) {
                    continue;
                }
                $fields = wp_is_numeric_array($fields) ? $fields : (array) ($fields['fields'] ?? array());
                /**
                 * Filters the fields for a settings group.
                 *
                 * @since 1.0.0
                 * @param array<int|string, mixed> $fields Field declarations for the group.
                 */
                $fields = (array) $this->app->apply_filters($group . '_settings', $fields);
                $title = is_string($title) && '' !== $title ? $title : ucwords(str_replace(array('-', '_'), ' ', $group));
                foreach ($fields as $index => $field) {
                    $field = wp_parse_args((array) $field, array('id' => '', 'name' => '', 'type' => 'text', 'label' => '', 'desc' => '', 'placeholder' => '', 'default' => null, 'sanitize' => '', 'priority' => 10, 'options' => array(), 'no_option' => false, 'show_if' => '', 'attrs' => array()));
                    if ('' === $field['id'] && '' !== $field['name']) {
                        $field['id'] = $field['name'];
                    }
                    $field['group'] = $group;
                    $fields[$index] = $field;
                }
                uasort($fields, static fn($a, $b) => ($a['priority'] ?? 10) <=> ($b['priority'] ?? 10));
                $this->settings[$group] = array('group' => $group, 'title' => $title, 'fields' => $fields);
            }
        }
        return $this->settings;
    }
    /**
     * Get the groups.
     *
     * @since 1.0.0
     * @return array<string, string> Group labels keyed by group id.
     */
    public function get_groups(): array
    {
        $groups = array();
        foreach ($this->get_settings() as $id => $group) {
            $groups[$id] = $group['title'];
        }
        return $groups;
    }
    /**
     * Get a group's fields.
     *
     * @since 1.0.0
     * @param string $group Group id.
     * @return array<int, array<string, mixed>> Field declarations.
     */
    public function get_fields(string $group): array
    {
        return $this->get_settings()[$group]['fields'] ?? array();
    }
    /**
     * Get the current values.
     *
     * @since 1.0.0
     * @param array<int, array<string, mixed>> $fields Field declarations.
     * @return array<string, mixed> Saved values keyed by field name, falling back to defaults.
     */
    public function get_values(array $fields): array
    {
        $values = array();
        foreach ($fields as $field) {
            if (empty($field['name']) || !empty($field['no_option'])) {
                continue;
            }
            $values[$field['name']] = $this->app->options->get($field['name'], $field['default'] ?? null);
        }
        return $values;
    }
    /**
     * Save the field values.
     *
     * @since 1.0.0
     * @param array<int, array<string, mixed>> $fields Field declarations to save.
     * @param array<string, mixed>             $data Submitted values keyed by field name.
     * @return bool True on success.
     */
    public function save_fields(array $fields, array $data): bool
    {
        foreach ($fields as $field) {
            if (empty($field['name']) || !empty($field['no_option'])) {
                continue;
            }
            $name = $field['name'];
            $value = $data[$name] ?? null;
            $sanitize = $field['sanitize'] ?? '';
            $type = $field['type'] ?? 'text';
            if (!is_string($sanitize) && is_callable($sanitize)) {
                $value = call_user_func($sanitize, $value, $field);
            } elseif (is_string($sanitize) && '' !== $sanitize) {
                foreach (explode('|', $sanitize) as $rule) {
                    $rule = trim($rule);
                    if ('' === $rule) {
                        continue;
                    }
                    $params = array();
                    if (str_contains($rule, ':')) {
                        list($rule, $args) = explode(':', $rule, 2);
                        $params = array_map('trim', explode(',', $args));
                    }
                    $value = $this->app->request->sanitize_value($value, $rule, $params);
                }
            } else {
                switch ($type) {
                    case 'email':
                        $value = sanitize_email((string) $value);
                        break;
                    case 'url':
                        $value = esc_url_raw((string) $value);
                        break;
                    case 'number':
                        $value = is_numeric($value) ? $value + 0 : '';
                        break;
                    case 'textarea':
                        $value = sanitize_textarea_field((string) $value);
                        break;
                    case 'editor':
                        $value = wp_kses_post((string) $value);
                        break;
                    case 'checkbox':
                    case 'toggle':
                    case 'switch':
                        $value = in_array($value, array('yes', '1', 1, true), true) ? 'yes' : 'no';
                        break;
                    case 'multiselect':
                    case 'multicheck':
                    case 'checkboxes':
                        $value = array_map('sanitize_text_field', array_map('strval', (array) $value));
                        break;
                    default:
                        $value = $this->app->request->clean_value($value);
                        break;
                }
            }
            $this->app->options->update($name, $value);
        }
        return true;
    }
    /**
     * Define the settings.
     *
     * Override to declare the plugin's settings. Keyed by group; each group is
     * either a direct field array, or an array with a `title` and a `fields` key.
     *
     * Example:
     *
     *     return array(
     *         'general'  => array(
     *             array( 'name' => 'site_name', 'type' => 'text', 'label' => 'Site Name' ),
     *         ),
     *         'advanced' => array(
     *             'title'  => 'Advanced Options',
     *             'fields' => array(
     *                 array( 'name' => 'cache_ttl', 'type' => 'number', 'default' => 3600 ),
     *             ),
     *         ),
     *     );
     *
     * @since 1.0.0
     * @return array<string, mixed> Settings definition keyed by group.
     */
    protected function define_settings(): array
    {
        return array();
    }
}