<?php

namespace PluginEver\VariationImages\Admin;

use PluginEver\VariationImages\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the premium version prompts.
 *
 * Remove this file when the plugin ships without a premium version.
 *
 * @since   1.0.0
 * @package PluginEver\VariationImages\Admin
 */
class Premium extends Component {

	/**
	 * Whether to load.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function autoload(): bool {
		return ! $this->app->is_pro_active();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_notices' ) );
		$this->app->on_action( 'settings_sidebar', array( $this, 'render_sidebar' ) );
	}

	/**
	 * Register the Pro notices.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_notices(): void {
		$this->app->notices->add(
			array(
				'notice_id' => 'wc_variation_images_upgrade',
				'type'      => 'info',
				'class'     => 'wc-variation-images-notice',
				'message'   => $this->app->templates_path( 'admin/notices/upgrade.php' ),
			)
		);

		$this->app->notices->add(
			array(
				'notice_id' => 'wc_starter_plugin_offer',
				'type'      => 'info',
				'class'     => 'wc-variation-images-notice',
				'message'   => $this->app->templates_path( 'admin/notices/special-offer.php' ),
			)
		);
	}

	/**
	 * Render the settings sidebar panel.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_sidebar(): void {
		$this->app->template->render(
			'admin.pro-panel',
			array(
				'title'    => __( 'Upgrade to Pro', 'wc-variation-images' ),
				'features' => $this->get_features(),
				'url'      => wc_variation_images_upgrade_url( 'settings_sidebar', 'banner' ),
				'label'    => __( 'Get Pro', 'wc-variation-images' ),
			)
		);
	}

	/**
	 * Get the premium feature highlights.
	 *
	 * @since 1.0.0
	 * @return array<int, string> Feature labels.
	 */
	protected function get_features(): array {
		return array(
			__( 'Unlimited images per variation.', 'wc-variation-images' ),
			__( 'Lightbox: fullscreen, slider play & social sharing.', 'wc-variation-images' ),
			__( 'Slider: loop, delay, navigation & item gap.', 'wc-variation-images' ),
			__( 'Gallery layouts: bottom, right, left & top.', 'wc-variation-images' ),
			__( 'Priority support & future updates.', 'wc-variation-images' ),
		);
	}
}
