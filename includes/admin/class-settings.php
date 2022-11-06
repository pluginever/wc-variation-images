<?php

namespace WC_Variation_Images\Admin;

use WC_Variation_Images\Framework;

defined( 'ABSPATH' ) || exit();

/**
 * Settings class.
 *
 * @since 1.0.0
 * @package WC_Variation_Images\Admin
 */
class Settings extends Framework\Settings {
	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 55 );
		add_action( 'wc_variation_images_settings_tabs', array( $this, 'add_extra_tabs' ), 20 );
		add_action( 'wc_variation_images_activated', array( $this, 'save_defaults' ) );
		add_action( 'wc_variation_images_settings_sidebar', array( $this, 'output_upgrade_widget' ) );
		add_action( 'wc_variation_images_settings_sidebar', array( $this, 'output_about_widget' ) );
		add_action( 'wc_variation_images_settings_sidebar', array( $this, 'output_help_widget' ) );
		add_action( 'wc_variation_images_settings_sidebar', array( $this, 'output_recommended_widget' ) );
	}

	/**
	 * Admin menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		$load = add_submenu_page(
			'woocommerce',
			__( 'Starter Plugin', 'wc-variation-images' ),
			__( 'Starter Plugin', 'wc-variation-images' ),
			'manage_options',
			'wc-variation-images',
			array( $this, 'output' )
		);
		add_action( 'load-' . $load, array( $this, 'save_settings' ) );
	}

	/**
	 * Add extra tabs.
	 *
	 * @since 1.0.0
	 */
	public function add_extra_tabs() {
		if ( $this->get_plugin()->get_doc_url() ) {
			echo '<a href="' . esc_url( $this->get_plugin()->get_doc_url() ) . '" target="_blank" class="nav-tab">' . esc_html__( 'Documentation', 'wc-variation-images' ) . '</a>';
		}
	}

	/**
	 * Get tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings_tabs() {
		$tabs = [
			'general'  => __( 'General', 'wc-variation-images' ),
			'advanced' => __( 'Advanced', 'wc-variation-images' ),
		];

		return apply_filters( 'wc_variation_images_settings_tabs_array', $tabs );
	}

	/**
	 * Get general settings.
	 *
	 * @since 1.0.0
	 * @return array General settings.
	 */
	public function get_general_tab_settings() {
		return array(
			array(
				'type'     => 'title',
				'id'       => 'general_settings',
				'title'    => __( 'Section title', 'wc-variation-images' ),
				'desc'     => sprintf(
				/* translators: %s: link to the documentation */
					__( 'These are the example description for the general settings. You can add more settings here. <a href="%s" target="_blank">Learn more</a>', 'wc-variation-images' ),
					'https://docs.woocommerce.com/document/settings-api/'
				),
				'desc_tip' => __( 'This is a description tip', 'wc-variation-images' ),
			),
			array(
				'id'   => 'pluginever_license_key_' . $this->get_plugin()->get_item_id(),
				'type' => 'pluginever_license_key_' . $this->get_plugin()->get_item_id(),
			),
			array(
				'title'             => __( 'Example Text field', 'wc-variation-images' ),
				'type'              => 'text',
				'id'                => 'wc_variation_images_example_text_field',
				'desc'              => __( 'This is a description for the example text field.', 'wc-variation-images' ),
				'desc_tip'          => __( 'This is a description tip', 'wc-variation-images' ),
				'default'           => 'Default value',
				'placeholder'       => 'Placeholder value',
				'custom_attributes' => array(
					'maxlength' => 5,
				),
			),
			array(
				'title'             => __( 'Example Password field', 'wc-variation-images' ),
				'type'              => 'password',
				'id'                => 'wc_variation_images_example_password_field',
				'desc'              => __( 'This is a description for the example password field this also have a tooltip.', 'wc-variation-images' ),
				'desc_tip'          => __( 'This is a description tip', 'wc-variation-images' ),
				'default'           => 'Default value',
				'placeholder'       => 'Placeholder value',
				'custom_attributes' => array(
					'maxlength' => 5,
				),
			),
			array(
				'title'       => __( 'Example Datetime field', 'wc-variation-images' ),
				'type'        => 'datetime',
				'id'          => 'wc_variation_images_example_datetime_field',
				'desc'        => __( 'This is a description for the example datetime field this also have a tooltip.', 'wc-variation-images' ),
				'desc_tip'    => __( 'This is a description tip', 'wc-variation-images' ),
				'default'     => 'Default value',
				'placeholder' => 'Placeholder value',
			),
			array(
				'title'       => __( 'Example Datetime-local field', 'wc-variation-images' ),
				'type'        => 'datetime-local',
				'id'          => 'wc_variation_images_example_datetime_local_field',
				'desc'        => __( 'This is a description for the example datetime-local field this only have description not a tooltip.', 'wc-variation-images' ),
				'default'     => '2020-01-01T00:00:00',
				'placeholder' => 'Placeholder value',
			),

			array(
				'title'       => __( 'Example Date field', 'wc-variation-images' ),
				'type'        => 'date',
				'id'          => 'wc_variation_images_example_date_field',
				'desc'        => __( 'This is a description for the example date field this only have description not a tooltip.', 'wc-variation-images' ),
				'default'     => '2020-01-01',
				'placeholder' => 'Placeholder value',
			),

			array(
				'title'       => __( 'Example Month field', 'wc-variation-images' ),
				'type'        => 'month',
				'id'          => 'wc_variation_images_example_month_field',
				'desc'        => __( 'This is a description for the example month field this only have description not a tooltip.', 'wc-variation-images' ),
				'default'     => '2020-01',
				'placeholder' => 'Placeholder value',
			),

			array(
				'title'       => __( 'Example Time field', 'wc-variation-images' ),
				'type'        => 'time',
				'id'          => 'wc_variation_images_example_time_field',
				'desc'        => __( 'This is a description for the example time field this only have description not a tooltip.', 'wc-variation-images' ),
				'default'     => '00:00:00',
				'placeholder' => 'Placeholder value',
			),

			array(
				'title'       => __( 'Example Week field', 'wc-variation-images' ),
				'type'        => 'week',
				'id'          => 'wc_variation_images_example_week_field',
				'desc'        => __( 'This is a description for the example week field this only have description not a tooltip.', 'wc-variation-images' ),
				'default'     => '2020-W01',
				'placeholder' => 'Placeholder value',
			),

			array(
				'title'             => __( 'Example Number field', 'wc-variation-images' ),
				'type'              => 'number',
				'id'                => 'wc_variation_images_example_number_field',
				'desc'              => __( 'Default value is 10 and min value is 0 and max value is 100 and step is 5.', 'wc-variation-images' ),
				'default'           => '10',
				'placeholder'       => 'Placeholder value',
				'custom_attributes' => array(
					'min'  => 0,
					'max'  => 100,
					'step' => 5,
				),
			),

			array(
				'title'       => __( 'Example Email field', 'wc-variation-images' ),
				'type'        => 'email',
				'id'          => 'wc_variation_images_example_email_field',
				'desc'        => __( 'Default value is admin email.', 'wc-variation-images' ),
				'default'     => get_option( 'admin_email' ),
				'placeholder' => 'john@doe.com',
			),

			array(
				'title'       => __( 'Example URL field', 'wc-variation-images' ),
				'type'        => 'url',
				'id'          => 'wc_variation_images_example_url_field',
				'desc'        => __( 'Default value is site url.', 'wc-variation-images' ),
				'default'     => get_option( 'siteurl' ),
				'placeholder' => 'https://example.com',
			),

			array(
				'title'       => __( 'Example Tel field', 'wc-variation-images' ),
				'type'        => 'tel',
				'id'          => 'wc_variation_images_example_tel_field',
				'desc'        => __( 'Default value is +8801234567890.', 'wc-variation-images' ),
				'default'     => '+8801234567890',
				'placeholder' => '+8801234567890',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'general_settings',
				'desc' => __( 'This is the end of the general settings section.', 'wc-variation-images' ),
			),

			array(
				'title' => __( 'Advanced Settings', 'wc-variation-images' ),
				'type'  => 'title',
				'desc'  => __( 'This is the start of the advanced settings section.', 'wc-variation-images' ),
				'id'    => 'advanced_settings',
			),

			array(
				'title'       => __( 'Example Textarea field', 'wc-variation-images' ),
				'type'        => 'textarea',
				'id'          => 'wc_variation_images_example_textarea_field',
				'desc'        => __( 'This is a description for the example textarea field this only have description not a tooltip.', 'wc-variation-images' ),
				'default'     => 'Default value',
				'placeholder' => 'Placeholder value',
			),

			// end of advanced settings section.
			array(
				'type' => 'sectionend',
				'id'   => 'advanced_settings',
				'desc' => __( 'This is the end of the advanced settings section.', 'wc-variation-images' ),
			),
		);
	}
}
