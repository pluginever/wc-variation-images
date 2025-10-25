<?php

namespace WooCommerceVariationImages\Admin;

defined( 'ABSPATH' ) || exit;

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
		$installed_time = absint( get_option( 'wc_variation_images_installed' ) );
		$current_time   = absint( wp_date( 'U' ) );

		// phpcs:disable
		// TODO: Uncomment the below code when halloween offer is over.
		/*
		if ( ! defined( 'WCVI_PRO_VERSION' ) ) {
			wc_variation_images()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/upgrade.php',
					'notice_id'   => 'wc_variation_images_upgrade',
					'style'       => 'border-left-color: #0542fa;',
					'dismissible' => false,
				)
			);
		}
		*/
		// phpcs:enable

		// Halloween offer notice.
		$halloween_end_time = date_i18n( strtotime( '2025-11-08 00:00:00' ) );
		if ( $current_time < $halloween_end_time ) {
			wc_variation_images()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/halloween.php',
					'dismissible' => false,
					'notice_id'   => 'wc_variation_images_halloween_promo_2025',
					'style'       => 'border-left-color: #8500ff;',
					'class'       => 'notice-halloween',
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
