<?php

namespace PluginEver\VariationImages\B8;

defined('ABSPATH') || exit;
/**
 * Handles the settings page.
 *
 * Renders a tabbed settings screen from the Settings service and persists
 * its submissions back through it.
 *
 * @since   1.0.0
 * @package \B8
 */
class SettingsUI extends Component
{
    /**
     * Admin-post action name for the settings form.
     *
     * @since 1.0.0
     * @var string
     */
    protected string $action = '';
    /**
     * Capability required to manage the settings.
     *
     * @since 1.0.0
     * @var string
     */
    protected string $capability = 'manage_options';
    /**
     * Constructor.
     *
     * @since 1.0.0
     * @param App $app Application instance.
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        if ($this->autoload()) {
            $this->action = $this->app->hook_name('save_settings');
            add_action('admin_post_' . $this->action, array($this, 'handle_save'));
        }
    }
    /**
     * Handle the settings form submission.
     *
     * @since 1.0.0
     * @return void
     */
    public function handle_save(): void
    {
        if (!current_user_can($this->capability)) {
            wp_die(esc_html('You are not allowed to save these settings.'));
        }
        check_admin_referer($this->action);
        $tab = isset($_POST['tab']) ? sanitize_text_field(wp_unslash($_POST['tab'])) : '';
        $fields = $this->app->settings->get_fields($tab);
        if (!empty($fields) && $this->save_fields($fields, wp_unslash($_POST))) {
            $this->app->flash->success('Settings saved.');
        }
        wp_safe_redirect(add_query_arg('tab', $tab, wp_get_referer()));
        exit;
    }
    /**
     * Render the settings page.
     *
     * @since 1.0.0
     * @return void
     */
    public function render(): void
    {
        $tabs = $this->app->settings->get_groups();
        if (empty($tabs)) {
            return;
        }
        $page_in = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS);
        $tab_in = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS);
        $page = !empty($page_in) ? sanitize_text_field(wp_unslash($page_in)) : '';
        $tab = !empty($tab_in) ? sanitize_text_field(wp_unslash($tab_in)) : '';
        if ('' !== $tab && !array_key_exists($tab, $tabs) && !headers_sent()) {
            wp_safe_redirect(add_query_arg(array('tab' => array_key_first($tabs))));
            exit;
        }
        if (!array_key_exists($tab, $tabs)) {
            $tab = (string) array_key_first($tabs);
        }
        wp_enqueue_style('b8-components');
        wp_enqueue_style('b8-layout');
        wp_enqueue_script('b8-settings');
        /**
         * Filters the settings page wrapper classes.
         *
         * @since 1.0.0
         * @param array<int, string> $classes Wrapper class names.
         */
        $classes = (array) $this->app->apply_filters('settings_wrap_classes', array('wrap', 'b8-wrap'));
        ?>
		<div class="<?php 
        echo esc_attr(implode(' ', array_map('sanitize_html_class', $classes)));
        ?>">
			<h1><?php 
        echo esc_html(get_admin_page_title());
        ?></h1>
			<nav class="nav-tab-wrapper b8-navbar">
				<?php 
        foreach ($tabs as $id => $label) {
            printf('<a href="%1$s" class="nav-tab %2$s">%3$s</a>', esc_url(admin_url('admin.php?page=' . $page . '&tab=' . $id)), esc_attr($tab === $id ? 'nav-tab-active' : ''), esc_html($label));
        }
        /**
         * Fires inside the settings nav, after the declared tabs.
         *
         * @since 1.0.0
         * @param array<string, string> $tabs Tab labels keyed by tab id.
         */
        $this->app->do_action('settings_nav_extras', $tabs);
        ?>
			</nav>
			<hr class="wp-header-end">

			<div class="b8-poststuff">
				<div class="column-1">
					<?php 
        $this->render_content($tab);
        ?>
				</div>
				<div class="column-2">
					<?php 
        $this->render_sidebar();
        /**
         * Fires inside the settings sidebar column.
         *
         * @since 1.0.0
         */
        $this->app->do_action('settings_sidebar');
        ?>
				</div>
			</div>
		</div>
		<?php 
    }
    /**
     * Render the content area for the current tab.
     *
     * @since 1.0.0
     * @param string $tab Current tab id.
     * @return void
     */
    protected function render_content(string $tab): void
    {
        /**
         * Fires inside the content area for a settings tab.
         *
         * @since 1.0.0
         */
        $this->app->do_action("settings_tab_{$tab}");
        $fields = $this->prepare_fields($this->app->settings->get_fields($tab));
        if (empty($fields)) {
            return;
        }
        ?>
		<form method="post" id="mainform" action="<?php 
        echo esc_url(admin_url('admin-post.php'));
        ?>" enctype="multipart/form-data">
			<input type="hidden" name="action" value="<?php 
        echo esc_attr($this->action);
        ?>"/>
			<input type="hidden" name="tab" value="<?php 
        echo esc_attr($tab);
        ?>"/>
			<?php 
        wp_nonce_field($this->action);
        $this->render_fields($fields);
        submit_button();
        ?>
		</form>
		<?php 
    }
    /**
     * Render the settings sidebar column.
     *
     * @since 1.0.0
     * @return void
     */
    protected function render_sidebar(): void
    {
        // Override to render the sidebar.
    }
    /**
     * Render the settings fields.
     *
     * Override to render the fields through a different handler.
     *
     * @since 1.0.0
     * @param array<int, array<string, mixed>> $fields Prepared field declarations.
     * @return void
     */
    protected function render_fields(array $fields): void
    {
        echo '<table class="form-table" role="presentation">';
        foreach ($fields as $field) {
            $attrs = implode(' ', array_map(static fn($key, $value) => sprintf('%s="%s"', esc_attr((string) $key), esc_attr($value)), array_keys($field['attrs']), $field['attrs']));
            $desc = '' !== $field['desc'] ? '<p class="description">' . $field['desc'] . '</p>' : '';
            switch ($field['type']) {
                case 'title':
                    echo '<tr><td colspan="2" style="padding-left:0;">';
                    if ('' !== $field['label']) {
                        printf('<h2 class="%2$s" style="margin-bottom:5px;%3$s">%1$s</h2>', esc_html($field['label']), esc_attr($field['class']), esc_attr($field['css']));
                    }
                    if ('' !== $field['desc']) {
                        printf('<p>%s</p>', wp_kses_post($field['desc']));
                    }
                    echo '</td></tr>';
                    break;
                case 'sectionend':
                    break;
                case 'html':
                    printf('<tr><td colspan="2" style="padding-left:0;">%s</td></tr>', wp_kses_post($field['desc']));
                    break;
                case 'notice':
                    printf('<tr><td colspan="2" style="padding-left:0;"><div class="notice notice-%1$s inline">%2$s</div></td></tr>', esc_attr(!empty($field['notice']) ? $field['notice'] : 'info'), wp_kses_post(wpautop($field['desc'])));
                    break;
                case 'slot':
                    printf('<tr><td colspan="2" style="padding-left:0;"><div id="%s"></div></td></tr>', esc_attr($field['id']));
                    break;
                case 'text':
                case 'password':
                case 'email':
                case 'url':
                case 'tel':
                case 'number':
                case 'date':
                case 'datetime-local':
                case 'month':
                case 'week':
                case 'time':
                    $value = $this->app->options->get($field['name'], $field['default']);
                    printf('<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="%3$s" name="%4$s" id="%1$s" value="%5$s" placeholder="%6$s" class="regular-text %7$s" style="%8$s" %9$s />', esc_attr($field['id']), esc_html($field['label']), esc_attr($field['type']), esc_attr($field['name']), esc_attr((string) $value), esc_attr($field['placeholder']), esc_attr($field['class']), esc_attr($field['css']), wp_kses_post($attrs));
                    echo wp_kses_post($desc);
                    echo '</td></tr>';
                    break;
                case 'color':
                    wp_enqueue_style('wp-color-picker');
                    wp_enqueue_script('wp-color-picker');
                    $value = $this->app->options->get($field['name'], $field['default']);
                    printf('<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="text" name="%3$s" id="%1$s" value="%4$s" placeholder="%5$s" class="b8-color-picker %6$s" style="%7$s" %8$s />', esc_attr($field['id']), esc_html($field['label']), esc_attr($field['name']), esc_attr((string) $value), esc_attr($field['placeholder']), esc_attr($field['class']), esc_attr($field['css']), wp_kses_post($attrs));
                    echo wp_kses_post($desc);
                    echo '</td></tr>';
                    break;
                case 'textarea':
                    $value = $this->app->options->get($field['name'], $field['default']);
                    printf('<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><textarea name="%3$s" id="%1$s" rows="5" class="large-text %4$s" style="%5$s" placeholder="%6$s" %7$s>%8$s</textarea>', esc_attr($field['id']), esc_html($field['label']), esc_attr($field['name']), esc_attr($field['class']), esc_attr($field['css']), esc_attr($field['placeholder']), wp_kses_post($attrs), esc_textarea((string) $value));
                    echo wp_kses_post($desc);
                    echo '</td></tr>';
                    break;
                case 'select':
                case 'multiselect':
                    $value = $this->app->options->get($field['name'], $field['default']);
                    $multiple = 'multiselect' === $field['type'];
                    $selected = array_map('strval', $multiple ? (array) $value : array($value));
                    printf('<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><select name="%3$s%4$s" id="%1$s" class="regular-text %5$s" style="%6$s"%7$s %8$s>', esc_attr($field['id']), esc_html($field['label']), esc_attr($field['name']), $multiple ? '[]' : '', esc_attr($field['class']), esc_attr($field['css']), $multiple ? ' multiple' : '', wp_kses_post($attrs));
                    foreach ($field['options'] as $key => $label) {
                        printf('<option value="%1$s"%2$s>%3$s</option>', esc_attr((string) $key), selected(in_array((string) $key, $selected, true), true, false), esc_html($label));
                    }
                    echo '</select>';
                    echo wp_kses_post($desc);
                    echo '</td></tr>';
                    break;
                case 'radio':
                    $value = $this->app->options->get($field['name'], $field['default']);
                    printf('<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><fieldset id="%1$s"><legend class="screen-reader-text">%2$s</legend>', esc_attr($field['id']), esc_html($field['label']));
                    foreach ($field['options'] as $key => $label) {
                        printf('<label><input type="radio" name="%1$s" value="%2$s"%3$s %4$s /> %5$s</label><br />', esc_attr($field['name']), esc_attr((string) $key), checked((string) $value, (string) $key, false), wp_kses_post($attrs), esc_html($label));
                    }
                    echo '</fieldset>';
                    echo wp_kses_post($desc);
                    echo '</td></tr>';
                    break;
                case 'checkbox':
                    $value = $this->app->options->get($field['name'], $field['default']);
                    printf('<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><label><input type="checkbox" name="%3$s" id="%1$s" value="yes"%4$s %5$s /> %6$s</label></td></tr>', esc_attr($field['id']), esc_html($field['label']), esc_attr($field['name']), checked('yes', $value, false), wp_kses_post($attrs), wp_kses_post($field['desc']));
                    break;
                case 'checkboxes':
                    $value = $this->app->options->get($field['name'], $field['default']);
                    $checked = array_map('strval', (array) $value);
                    printf('<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><fieldset id="%1$s"><legend class="screen-reader-text">%2$s</legend>', esc_attr($field['id']), esc_html($field['label']));
                    foreach ($field['options'] as $key => $label) {
                        printf('<label><input type="checkbox" name="%1$s[]" value="%2$s"%3$s %4$s /> %5$s</label><br />', esc_attr($field['name']), esc_attr((string) $key), checked(in_array((string) $key, $checked, true), true, false), wp_kses_post($attrs), wp_kses_post($label));
                    }
                    echo '</fieldset>';
                    echo wp_kses_post($desc);
                    echo '</td></tr>';
                    break;
                case 'editor':
                    $value = $this->app->options->get($field['name'], $field['default']);
                    $editor = wp_parse_args((array) $field['editor'], array('textarea_name' => $field['name']));
                    printf('<tr><th scope="row"><label for="%1$s">%2$s</label></th><td>', esc_attr($field['id']), esc_html($field['label']));
                    wp_editor((string) $value, $field['id'], $editor);
                    echo wp_kses_post($desc);
                    echo '</td></tr>';
                    break;
                default:
                    $value = $this->app->options->get($field['name'], $field['default']);
                    /**
                     * Fires to render a custom settings field type.
                     *
                     * @since 1.0.0
                     * @param array $field Prepared field declaration.
                     * @param mixed $value Current field value.
                     */
                    $this->app->do_action('settings_field_' . $field['type'], $field, $value);
                    break;
            }
        }
        echo '</table>';
    }
    /**
     * Persist the submitted settings fields.
     *
     * Override to persist through a different handler.
     *
     * @since 1.0.0
     * @param array<int, array<string, mixed>> $fields Field declarations for the current tab.
     * @param array<string, mixed>             $data   Unslashed request data.
     * @return bool True when the fields were saved.
     */
    protected function save_fields(array $fields, array $data): bool
    {
        return $this->app->settings->save_fields($fields, $data);
    }
    /**
     * Prepare the fields for rendering.
     *
     * @since 1.0.0
     * @param array<int, array<string, mixed>> $fields Field declarations.
     * @return array<int, array<string, mixed>> Prepared field declarations.
     */
    protected function prepare_fields(array $fields): array
    {
        foreach ($fields as $key => $field) {
            $field = wp_parse_args($field, array('class' => '', 'css' => '', 'editor' => array(), 'attrs' => array()));
            foreach ($field as $prop => $value) {
                if (empty($prop) || empty($value)) {
                    continue;
                }
                $encoded = is_array($value) || is_object($value) ? wp_json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) : $value;
                if (str_starts_with($prop, 'attr-')) {
                    $field['attrs'][substr($prop, 5)] = $encoded;
                } elseif (str_starts_with($prop, 'data-')) {
                    $field['attrs'][$prop] = $encoded;
                } elseif ('show_if' === $prop) {
                    $field['attrs']['data-show-if'] = $encoded;
                } elseif (in_array($prop, array('maxlength', 'pattern', 'readonly', 'disabled', 'required', 'autofocus'), true)) {
                    $field['attrs'][$prop] = $prop;
                }
            }
            $fields[$key] = $field;
        }
        return $fields;
    }
}