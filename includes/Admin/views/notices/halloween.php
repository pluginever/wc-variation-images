<?php
/**
 * Admin notice: Halloween offer.
 *
 * @since 1.3.2
 * @return void
 *
 * @package WooCommerceVariationImages/Admin/Views/Notices
 */

defined( 'ABSPATH' ) || exit;

$plugin_url = defined( 'WCVI_PRO_VERSION' ) ? trailingslashit( wc_variation_images()->author_uri ) . 'plugins/' : trailingslashit( wc_variation_images()->plugin_uri );

?>
<div class="notice-body">
	<div class="notice-icon">
		<img src="<?php echo esc_url( wc_variation_images()->get_assets_url( 'images/halloween-icon.svg' ) ); ?>" alt="Variation Images Halloween Offer">
	</div>
	<div class="notice-content">
		<h3>
			<?php esc_html_e( 'Limited Time Offer! PluginEver Halloween Sale: 30% OFF!!', 'wc-variation-images' ); ?>
		</h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					// translators: 1.Offer Percentage, 2. Coupon Code.
					__( 'Spectacular Halloween Deal! Get %1$s on all premium plugins with code %2$s. Don\'t miss out — this offer vanishes soon! 👻', 'wc-variation-images' ),
					'<strong>' . esc_attr( '30% OFF' ) . '</strong>',
					'<strong>' . esc_attr( 'EVERSAVE30' ) . '</strong>'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<div class="footer-btn">
		<a href="<?php echo esc_url( trailingslashit( $plugin_url ) . '?utm_source=plugin&utm_medium=notice&utm_campaign=halloween-sale&discount=EVERSAVE30' ); ?>" class="primary halloween-upgrade-btn" target="_blank">
			<span class="dashicons dashicons-cart"></span>
			<?php esc_html_e( 'Claim your discount!!', 'wc-variation-images' ); ?>
		</a>
		<a href="#" class="halloween-remind-btn" data-snooze="<?php echo esc_attr( WEEK_IN_SECONDS ); ?>">
			<span class="dashicons dashicons-clock"></span>
			<?php esc_html_e( 'Remind me later', 'wc-variation-images' ); ?>
		</a>
		<a href="#" class="primary halloween-remove-btn" data-dismiss>
			<span class="dashicons dashicons-remove"></span>
			<?php esc_html_e( 'Never show this again!', 'wc-variation-images' ); ?>
		</a>
		<a href="#" class="primary halloween-dismiss-btn" data-dismiss>
			<span class="dashicons dashicons-dismiss"></span>
			<?php esc_html_e( 'DISMISS', 'wc-variation-images' ); ?>
		</a>
	</div>
	<strong class="halloween-footer-text"><?php esc_html_e( 'Valid until November 8, 2025', 'wc-variation-images' ); ?></strong>
</div>
