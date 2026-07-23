<?php

namespace PluginEver\VariationImages\Admin;

use PluginEver\VariationImages\B8\SettingsUI;
use PluginEver\VariationImages\Controllers\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings.
 *
 * @since   1.0.0
 * @package PluginEver\VariationImages\Admin
 */
class Settings extends SettingsUI {

	/**
	 * Capability required to manage the settings.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $capability = 'manage_woocommerce';

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		$this->app->on_filter( 'admin_pages', array( $this, 'register_page' ) );
		$this->app->on_filter( 'settings_wrap_classes', array( $this, 'wrap_classes' ) );
		$this->app->on_action( 'settings_nav_extras', array( $this, 'output_docs_tab' ) );
		$this->app->on_filter( 'settings', array( $this, 'register_settings' ) );
	}

	/**
	 * Filter the admin pages.
	 *
	 * @since 1.0.0
	 * @param array<int, array<string, mixed>> $pages Admin page configurations.
	 * @return array<int, array<string, mixed>>
	 */
	public function register_page( array $pages ): array {
		$pages[] = array(
			'title'    => __( 'Variation Images', 'wc-variation-images' ),
			'slug'     => 'wc-variation-images-settings',
			'callback' => array( $this, 'render' ),
			'position' => 55,
		);

		return $pages;
	}

	/**
	 * Add the WooCommerce class to the settings page wrapper.
	 *
	 * @since 1.0.0
	 * @param array<int, string> $classes Wrapper class names.
	 * @return array<int, string>
	 */
	public function wrap_classes( array $classes ): array {
		$classes[] = 'woocommerce';

		return $classes;
	}

	/**
	 * Register the plugin settings.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Settings definition keyed by tab.
	 * @return array<string, mixed>
	 */
	public function register_settings( array $settings ): array {
		$settings['general'] = array(
			'title'  => __( 'General', 'wc-variation-images' ),
			'fields' => array(
				array(
					'title' => __( 'Gallery settings', 'wc-variation-images' ),
					'type'  => 'title',
					'desc'  => __( 'The following options affect how the plugin will work.', 'wc-variation-images' ),
					'id'    => 'general_options',
					'name'  => 'general_options',
				),
				array(
					'id'       => 'wcvi_disable_image_zoom',
					'name'     => 'wcvi_disable_image_zoom',
					'title'    => __( 'Disable Image Zoom', 'wc-variation-images' ),
					'desc'     => __( 'Disable image zoom for variable product.', 'wc-variation-images' ),
					'desc_tip' => __( 'Check this box to disable the image zoom effect on hover for this product.', 'wc-variation-images' ),
					'type'     => 'select',
					'options'  => array(
						'no'  => __( 'No', 'wc-variation-images' ),
						'yes' => __( 'Yes', 'wc-variation-images' ),
					),
					'default'  => 'no',
				),
				array(
					'id'       => 'wcvi_disable_image_lightbox',
					'name'     => 'wcvi_disable_image_lightbox',
					'title'    => __( 'Disable Lightbox', 'wc-variation-images' ),
					'desc'     => __( 'Disable image lightbox for variable product.', 'wc-variation-images' ),
					'desc_tip' => __( 'Enable this option to hide the lightbox on the product page.', 'wc-variation-images' ),
					'type'     => 'select',
					'options'  => array(
						'no'  => __( 'No', 'wc-variation-images' ),
						'yes' => __( 'Yes', 'wc-variation-images' ),
					),
					'default'  => 'no',
				),
				array(
					'id'       => 'wcvi_disable_image_slider',
					'name'     => 'wcvi_disable_image_slider',
					'title'    => __( 'Disable Image Slider', 'wc-variation-images' ),
					'desc'     => __( 'Disable image slider for variable product.', 'wc-variation-images' ),
					'desc_tip' => __( 'Enable this option to hide the image slider for this specific variation on the frontend.', 'wc-variation-images' ),
					'type'     => 'select',
					'options'  => array(
						'no'  => __( 'No', 'wc-variation-images' ),
						'yes' => __( 'Yes', 'wc-variation-images' ),
					),
					'default'  => 'no',
				),
				array(
					'id'       => 'wcvi_gallery_position',
					'name'     => 'wcvi_gallery_position',
					'title'    => __( 'Gallery Position', 'wc-variation-images' ),
					'desc'     => __( 'Set product image position.', 'wc-variation-images' ),
					'desc_tip' => __( 'Select the position of the product gallery on the product page.', 'wc-variation-images' ),
					'type'     => 'select',
					'options'  => Helpers::gallery_position_list(),
					'default'  => 'bottom',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'general_options',
					'name' => 'general_options',
				),
			),
		);

		return $settings;
	}

	/**
	 * Output the documentation tab.
	 *
	 * @since 1.1.0
	 * @param array<string, string> $tabs Settings tabs.
	 * @return void
	 */
	public function output_docs_tab( $tabs ) {
		printf(
			'<a href="%s" class="nav-tab" target="_blank">%s</a>',
			esc_url( wc_variation_images()->docs_url ),
			esc_html__( 'Documentation', 'wc-variation-images' )
		);
	}

	/**
	 * Output the settings sidebar.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function render_sidebar(): void {
		?>
		<!--	Support Sidebar	-->
		<div class="b8-card promo-panel">
			<div class="b8-card__header">
				<h2 class="b8-card__title"><?php esc_html_e( 'Need Help ?', 'wc-variation-images' ); ?></h2>
			</div>
			<div class="b8-card__body">
				<ul>
					<li><a target="_blank" href="<?php echo esc_url( 'https://www.facebook.com/groups/pluginever' ); ?>"><?php esc_html_e( 'Join our Community', 'wc-variation-images' ); ?></a></li>
					<li><a target="_blank" href="<?php echo esc_url( 'https://www.pluginever.com/contact' ); ?>"><?php esc_html_e( 'Request a Feature', 'wc-variation-images' ); ?></a></li>
					<li><a target="_blank" href="<?php echo esc_url( 'https://www.pluginever.com/contact' ); ?>"><?php esc_html_e( 'Report a Bug', 'wc-variation-images' ); ?></a></li>
				</ul>
			</div>

		</div>
		<?php
	}

	/**
	 * Output the settings fields.
	 *
	 * @since 1.0.0
	 * @param array<int, array<string, mixed>> $fields Prepared field declarations.
	 * @return void
	 */
	protected function render_fields( array $fields ): void {
		if ( function_exists( 'woocommerce_admin_fields' ) ) {
			woocommerce_admin_fields( $fields );
			return;
		}

		parent::render_fields( $fields );
	}

	/**
	 * Persist the submitted settings fields.
	 *
	 * @since 1.0.0
	 * @param array<int, array<string, mixed>> $fields Field declarations for the current tab.
	 * @param array<string, mixed>             $data   Unslashed request data.
	 * @return bool True when the fields were saved.
	 */
	protected function save_fields( array $fields, array $data ): bool {
		if ( ! function_exists( 'woocommerce_update_options' ) ) {
			return false;
		}

		woocommerce_update_options( $fields );

		return true;
	}
}
