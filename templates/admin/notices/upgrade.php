<?php
/**
 * Upgrade notice.
 *
 * @since   1.0.0
 * @package PluginEver\VariationImages
 */

defined( 'ABSPATH' ) || exit;
?>
<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: 1: opening anchor tag, 2: closing anchor tag. */
			__( 'You are using the free version of Variation Images. %1$sUpgrade to Pro%2$s to unlock the full feature set.', 'wc-variation-images' ),
			'<a href="' . esc_url( wc_variation_images_upgrade_url( 'upgrade_notice', 'notice' ) ) . '" target="_blank" rel="noopener noreferrer"><strong>',
			'</strong></a>'
		)
	);
	?>
</p>
