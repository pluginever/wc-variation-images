<?php
/**
 * Admin notice: Black Friday offer.
 *
 * @package WooCommerceVariationImages
 * @since 2.0.3
 * @return void
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<div class="notice-body">
	<div class="notice-icon">
		<img src="<?php echo esc_url( wc_variation_images()->get_assets_url( 'images/black-friday-icon.svg' ) ); ?>" alt="WC Variation Images">
	</div>
	<div class="notice-content">
		<h3>
			<?php esc_html_e( 'Black Friday & Cyber Monday: Flat 40% OFF on All Premium Plugins!', 'wc-variation-images' ); ?>
		</h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					// translators: 1.Offer Percentage, 2. Coupon Code.
					__( 'Boost your WooCommerce store this Black Friday! Get %1$s on all premium plugins with code %2$s. Don’t miss this limited-time deal!', 'wc-variation-images' ),
					'<strong>' . esc_attr( '40% OFF' ) . '</strong>',
					'<strong>' . esc_attr( 'FLASH40' ) . '</strong>'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<div class="footer-btn">
		<a href="<?php echo esc_url( wc_variation_images()->plugin_uri . '?utm_source=plugin&utm_medium=notice&utm_campaign=black-friday-2024&discount=FLASH40' ); ?>" class="button button-primary black-friday-upgrade-btn" target="_blank">
			<span class="dashicons dashicons-cart"></span>
			<?php esc_html_e( 'Claim your 40% discount!!', 'wc-variation-images' ); ?>
		</a>
		<a href="#" data-snooze="<?php echo esc_attr( WEEK_IN_SECONDS ); ?>">
			<span class="dashicons dashicons-clock"></span>
			<?php esc_html_e( 'Remind me later', 'wc-variation-images' ); ?>
		</a>
		<a href="#" class="button black-friday-dismiss-btn" data-dismiss>
			<span class="dashicons dashicons-dismiss"></span>
			<span class="btn-text"><?php esc_html_e( 'DISMISS', 'wc-variation-images' ); ?></span>
		</a>
	</div>
	<span class="black-friday-footer-text"><?php esc_html_e( 'Valid until December 07, 2024', 'wc-variation-images' ); ?></span>
</div>
