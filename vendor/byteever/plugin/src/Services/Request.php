<?php

namespace PluginEver\VariationImages\B8\Services;

defined('ABSPATH') || exit;
/**
 * Handles request data.
 *
 * Provides data cleaning via pipe-separated rule strings.
 *
 * @since 1.0.0
 * @package \B8
 */
class Request
{
    /**
     * Sanitize the data.
     *
     * @since 1.0.0
     *
     * @param array<string, mixed>  $data Data to sanitize.
     * @param array<string, string> $rules Sanitization rules (field => rule_string).
     *
     * @return array<string, mixed> Sanitized data.
     */
    public function sanitize($data, $rules): array
    {
        $sanitized = array();
        foreach ($data as $field => $value) {
            $value = $value ?? '';
            if (isset($rules[$field])) {
                foreach (explode('|', $rules[$field]) as $rule_item) {
                    $rule_item = trim($rule_item);
                    if (empty($rule_item)) {
                        continue;
                    }
                    $parameters = array();
                    if (str_contains($rule_item, ':')) {
                        list($rule_name, $params_string) = explode(':', $rule_item, 2);
                        $parameters = array_map('trim', explode(',', $params_string));
                    } else {
                        $rule_name = $rule_item;
                    }
                    $value = $this->sanitize_value($value, $rule_name, $parameters);
                }
                $sanitized[$field] = $value;
            } else {
                $sanitized[$field] = $this->clean_value($value);
            }
        }
        return $sanitized;
    }
    /**
     * Sanitize a value.
     *
     * Supports callable rules for custom sanitization (e.g. 'my_custom_sanitizer').
     *
     * @since 1.0.0
     *
     * @param mixed              $value Value to sanitize.
     * @param string             $rule Rule name.
     * @param array<int, string> $parameters Rule parameters.
     *
     * @return mixed Sanitized value.
     */
    public function sanitize_value($value, $rule, $parameters)
    {
        if (is_array($value)) {
            return array_map(fn($item) => $this->sanitize_value($item, $rule, $parameters), $value);
        }
        switch ($rule) {
            case 'textarea':
                return sanitize_textarea_field($value);
            case 'email':
                return strtolower(sanitize_email($value));
            case 'url':
                return esc_url_raw($value);
            case 'key':
                return sanitize_key($value);
            case 'filename':
                return sanitize_file_name($value);
            case 'slug':
                return sanitize_title_with_dashes($value);
            case 'title':
            case 'sanitize_title':
                return sanitize_title($value);
            case 'bool':
            case 'boolean':
                return (bool) filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'int':
            case 'integer':
                return intval($value);
            case 'float':
            case 'number':
                return (float) $value;
            case 'strip_tags':
                return wp_strip_all_tags($value);
            case 'trim':
                return trim((string) $value);
            case 'lower':
            case 'lowercase':
                return strtolower((string) $value);
            case 'upper':
            case 'uppercase':
                return strtoupper((string) $value);
            case 'ucfirst':
                return ucfirst(strtolower((string) $value));
            case 'ucwords':
            case 'title_case':
                return ucwords(strtolower((string) $value));
            case 'html':
                return wp_kses_post($value);
            case 'collapse_ws':
                return preg_replace('/\s+/', ' ', trim((string) $value));
            case 'json':
                return is_string($value) ? json_decode($value, true) ?? null : null;
            case 'alpha':
                return preg_replace('/[^a-zA-Z]/', '', (string) $value);
            case 'alpha_num':
                return preg_replace('/[^a-zA-Z0-9]/', '', (string) $value);
            case 'alpha_dash':
                return preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $value);
            case 'numeric':
                return preg_replace('/[^0-9.-]/', '', (string) $value);
            case 'limit':
                $limit = isset($parameters[0]) ? (int) $parameters[0] : 100;
                return mb_strlen((string) $value) <= $limit ? (string) $value : mb_substr((string) $value, 0, $limit) . ($parameters[1] ?? '');
            case 'text':
            default:
                return is_callable($rule) ? call_user_func($rule, $value) : $this->clean_value($value);
        }
    }
    /**
     * Clean a value.
     *
     * @since 1.0.0
     *
     * @param mixed $value Value to clean.
     *
     * @return mixed Cleaned value.
     */
    public function clean_value($value)
    {
        if (is_array($value)) {
            return map_deep($value, 'sanitize_text_field');
        }
        return is_scalar($value) ? sanitize_text_field($value) : $value;
    }
    /**
     * Get the client IP address.
     *
     * @since 1.0.0
     *
     * @param bool $anonymize Whether to anonymize the IP address (GDPR compliance).
     *
     * @return string Client IP address.
     */
    public function ip_address(bool $anonymize = false): string
    {
        $ip_headers = array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_headers as $header) {
            $ip = isset($_SERVER[$header]) ? sanitize_text_field(wp_unslash($_SERVER[$header])) : '';
            if (!empty($ip)) {
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $anonymize ? wp_privacy_anonymize_ip($ip) : $ip;
                }
            }
        }
        $fallback_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '127.0.0.1';
        return $anonymize ? wp_privacy_anonymize_ip($fallback_ip) : $fallback_ip;
    }
}