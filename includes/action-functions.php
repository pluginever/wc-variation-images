<?php

/**
 * Control Woocommerce Gallery Settings
 *
 * since 1.0.0
 */
function control_wc_gallery() {
	global $post;
	$product = wc_get_product( $post->ID );
	if ( is_product() && 'variable' == $product->get_type() ) {
		$hide_zoom    = wpwvi_get_settings( 'wpwvi_hide_image_zoom', 'no', 'wpwvi_general_settings' );
		$hide_gallery = wpwvi_get_settings( 'wpwvi_hide_image_slider', 'no', 'wpwvi_general_settings' );;
		if ( 'yes' == $hide_zoom ) {
			remove_theme_support( 'wc-product-gallery-zoom' );
		}
		if ( 'yes' == $hide_gallery ) {
			remove_theme_support( 'wc-product-gallery-slider' );
		}

	}

}

add_action( 'wp', 'control_wc_gallery', 100 );
