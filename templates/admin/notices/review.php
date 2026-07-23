<?php
/**
 * Review request notice.
 *
 * @since   1.0.0
 * @package PluginEver\StarterPlugin
 */

defined( 'ABSPATH' ) || exit;
?>
<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: 1: opening anchor tag, 2: closing anchor tag. */
			__( 'Enjoying WC Starter Plugin? A %1$sfive-star review%2$s helps other store owners find it and means a lot to our team.', 'wc-starter-plugin' ),
			'<a href="' . esc_url( (string) wc_starter_plugin()->review_url ) . '" target="_blank" rel="noopener noreferrer"><strong>',
			'</strong></a>'
		)
	);
	?>
</p>
