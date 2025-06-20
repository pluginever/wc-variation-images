<?php
/**
 * Admin notice for upgrade.
 *
 * @since 1.0.0
 * @return void
 * @package WooCommerceVariationImages\Admin\Notices
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="notice-body">
	<div class="notice-icon">
		<img src="<?php echo esc_attr( wc_variation_images()->get_assets_url( 'images/plugin-icon.png' ) ); ?>" alt="WC Variation Images">
	</div>
	<div class="notice-content">
		<h3><?php esc_attr_e( 'Flash Sale Alert!', 'wc-variation-images' ); ?></h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					// translators: %1$s: WC Variation Images Pro link, %2$s: Coupon code.
					__( 'Enjoy <strong>10%% discount</strong> on %1$s! Use coupon code %2$s at checkout to grab the deal. Don’t miss out — this offer won’t last forever!', 'wc-variation-images' ),
					'<a href="https://pluginever.com/plugins/wc-variation-images-pro/?utm_source=plugin&utm_medium=notice&utm_campaign=flash-sale" target="_blank"><strong>WC Variation Images Pro</strong></a>',
					'<strong>FLASH10</strong>'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<a class="primary" href="https://pluginever.com/plugins/wc-variation-images-pro/?utm_source=plugin&utm_medium=notice&utm_campaign=flash-sale" target="_blank">
		<span class="dashicons dashicons-cart"></span>
		<?php esc_attr_e( 'Upgrade now', 'wc-variation-images' ); ?>
	</a>
	<a href="#" data-snooze="<?php echo esc_attr( WEEK_IN_SECONDS ); ?>">
		<span class="dashicons dashicons-clock"></span>
		<?php esc_attr_e( 'Maybe later', 'wc-variation-images' ); ?>
	</a>
</div>
