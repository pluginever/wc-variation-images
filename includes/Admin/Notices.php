<?php

namespace PluginEver\VariationImages\Admin;

use PluginEver\VariationImages\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the admin notices.
 *
 * @since   1.0.0
 * @package PluginEver\VariationImages\Admin
 */
class Notices extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_notices' ) );
	}

	/**
	 * Register the admin notices.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_notices(): void {
		$installed_on = strtotime( (string) $this->app->options->get( 'installed_on' ) );

		if ( $installed_on && ( time() - $installed_on ) > WEEK_IN_SECONDS ) {
			$this->app->notices->add(
				array(
					'notice_id' => 'wc_variation_images_review',
					'type'      => 'info',
					'class'     => 'wc-variation-images-notice',
					'message'   => $this->app->templates_path( 'admin/notices/review.php' ),
				)
			);
		}
	}
}
