<?php

namespace WooCommerceVariationImages;

use PHP_CodeSniffer\Generators\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Class Products.
 *
 * @since 1.0.0
 *
 * @package WooCommerceVariationImages
 */
class Products {
	/**
	 * Products constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'woocommerce_single_product_image_gallery_classes', array( $this, 'add_gallery_class' ) );
		add_filter( 'wc_get_template', array( $this, 'gallery_template_override' ), 60, 2 );
		add_action( 'wp', array( $this, 'wc_variation_images_gallery_control' ), 100 );
		/** add_action( 'woocommerce_product_thumbnails', array( $this, 'replace_variable_product_image' ) ); */
	}

	/**
	 * Overwrite template.
	 *
	 * @param HTML   $template Template HTML.
	 * @param string $template_name Template name.
	 *
	 * @since 1.0.0
	 * @return mixed|null
	 */
	public function gallery_template_override( $template, $template_name ) {
		$old_template = $template;

		// Disable gallery on specific product.
		if ( apply_filters( 'disable_woo_variation_gallery', false ) ) {
			return $old_template;
		}

		$product = wc_get_product();
		if ( is_product() && 'variable' === $product->get_type() ) {
			if ( 'single-product/product-image.php' === $template_name ) {
				if ( 'no' === get_option( 'wcvi_disable_image_slider', 'no' ) ) {
					$template = WCVI_PLUGIN_TEMPLATES_DIR . '/product-image-slider.php';
				} else {
					$template = WCVI_PLUGIN_TEMPLATES_DIR . '/product-image.php';
				}
			}
		}

		return apply_filters( 'wc_variation_images_gallery_template_override', $template, $template_name, $old_template );
	}

	/**
	 * Replace Image.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function replace_variable_product_image() {
		global $product;

		if ( $product->is_type( 'variable' ) ) {
			$available_variations = $product->get_available_variations();

			foreach ( $available_variations as $variation ) {
				$variation_id = $variation['variation_id'];
				$image_id     = get_post_thumbnail_id( $variation_id );
				$image_url    = wp_get_attachment_url( $image_id );

				echo '<div class="custom-product-image" data-variation-id="' . esc_attr( $variation_id ) . '">';
				echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $product->get_name() ) . '" />';
				echo '<p>Custom HTML content for variation ' . esc_html( $variation['attributes']['attribute_pa_color'] ) . ' goes here.</p>';
				echo '</div>';
			}
		} elseif ( has_post_thumbnail( $product->get_id() ) ) {
				$image_url = wp_get_attachment_url( get_post_thumbnail_id( $product->get_id() ) );
				echo '<div class="custom-product-image">';
				echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $product->get_name() ) . '" />';
				echo '<p>Custom HTML content goes here.</p>';
				echo '</div>';
		}
	}

	/**
	 * Add class WooCommerce single product page.
	 *
	 * @param array $class_name Class names.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function add_gallery_class( $class_name ) {
		$class_name[] = sanitize_html_class( 'wcvi-product-gallery' );

		return $class_name;
	}

	/**
	 * Control Woocommerce Gallery Settings
	 *
	 * since 1.0.0
	 */
	public function wc_variation_images_gallery_control() {
		global $post;

		if ( ! $post ) {
			return;
		}

		$product = wc_get_product( $post->ID );
		if ( is_product() && 'variable' === $product->get_type() ) {
			$hide_zoom     = get_option( 'wcvi_disable_image_zoom', 'no' );
			$hide_lightbox = get_option( 'wcvi_disable_image_lightbox', 'no' );
			$hide_gallery  = get_option( 'wcvi_disable_image_slider', 'no' );
			if ( 'yes' === $hide_zoom ) {
				remove_theme_support( 'wc-product-gallery-zoom' );
			}
			if ( 'yes' === $hide_lightbox ) {
				remove_theme_support( 'wc-product-gallery-lightbox' );
			}
			if ( 'yes' === $hide_gallery ) {
				remove_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
