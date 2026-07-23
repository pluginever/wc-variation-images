<?php
/**
 * Limited-time offer notice.
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
			/* translators: 1: discount code, 2: opening anchor tag, 3: closing anchor tag. */
			__( 'Limited-time offer: save 10%% on Variation Images Pro with code %1$s. %2$sGrab the deal%3$s.', 'wc-variation-images' ),
			'<strong>FLASH10</strong>',
			'<a href="' . esc_url( wc_starter_plugin_upgrade_url( 'special_offer', 'notice' ) ) . '" target="_blank" rel="noopener noreferrer"><strong>',
			'</strong></a>'
		)
	);
	?>
</p>
