<?php

namespace WooCommerceVariationImages\Admin;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Notices class.
 *
 * @since 1.0.0
 * @package WooCommerceVariationImages\Admin
 */
class Notices {

	/**
	 * Notices constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {
		$installed_time = get_option( 'wc_variation_images_installed' );
		$current_time   = wp_date( 'U' );

		// Halloween offer notice.
		$halloween_time = date_i18n( strtotime( '2024-11-11 00:00:00' ) );
		if ( $current_time < $halloween_time ) {
			wp_enqueue_style( 'wc-variation-images-halloween' );
			wc_variation_images()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/halloween.php',
					'dismissible' => false,
					'notice_id'   => 'wc_variation_images_promotion',
					'style'       => 'border-left-color: #8500ff;',
					'class'       => 'notice-halloween',
				)
			);
		}

		if ( ! defined( 'WC_VARIATION_IMAGES_PRO_VERSION' ) ) {
			wc_variation_images()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/upgrade.php',
					'notice_id'   => 'wc_variation_images_upgrade',
					'style'       => 'border-left-color: #0542fa;',
					'dismissible' => false,
				)
			);
		}

		// Show after 5 days.
		if ( $installed_time && $current_time > ( $installed_time + ( 5 * DAY_IN_SECONDS ) ) ) {
			wc_variation_images()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/review.php',
					'dismissible' => false,
					'notice_id'   => 'wc_variation_images_review',
					'style'       => 'border-left-color: #0542fa;',
				)
			);
		}
	}
}
