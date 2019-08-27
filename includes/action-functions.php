<?php
defined( 'ABSPATH' ) || die();
/**
 * Control Woocommerce Gallery Settings
 *
 * since 1.0.0
 */
function control_wc_gallery() {
	global $post;
	$product = wc_get_product( $post->ID );
	if ( is_product() && 'variable' == $product->get_type() ) {
		$hide_zoom     = wcviget_settings( 'wcvihide_image_zoom', 'no', 'wcvigeneral_settings' );
		$hide_lightbox = wcviget_settings( 'wcvihide_image_lightbox', 'no', 'wcvigeneral_settings' );
		$hide_gallery  = wcviget_settings( 'wcvihide_image_slider', 'no', 'wcvigeneral_settings' );;
		if ( 'yes' == $hide_zoom ) {
			remove_theme_support( 'wc-product-gallery-zoom' );
		}
		if ( 'yes' == $hide_lightbox ) {
			remove_theme_support( 'wc-product-gallery-lightbox' );
		}
		if ( 'yes' == $hide_gallery ) {
			remove_theme_support( 'wc-product-gallery-slider' );

		}
	}

}

add_action( 'wp', 'control_wc_gallery', 100 );
