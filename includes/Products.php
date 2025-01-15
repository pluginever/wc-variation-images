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
		add_filter( 'woocommerce_single_product_image_gallery_classes', array( $this, 'wc_variation_images_add_gallery_class' ) );
		add_filter( 'wc_get_template', array( $this, 'gallery_template_override' ), 60, 2 );
		add_action( 'wp', array( $this, 'wc_variation_images_gallery_control' ), 100 );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'wc_variation_images_upload_images' ), 10, 3 );
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
				if ( 'no' === get_option( 'wc_variation_images_hide_image_slider', 'no' ) ) {
					$template = WC_VARIATION_IMAGES_TEMPLATES_DIR . '/product-image-slider.php';
				} else {
					$template = WC_VARIATION_IMAGES_TEMPLATES_DIR . '/product-image.php';
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
	public function wc_variation_images_add_gallery_class( $class_name ) {
		$class_name[] = sanitize_html_class( 'wc-variation-images-product-gallery' );
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
			$hide_zoom     = get_option( 'wc_variation_images_hide_image_zoom', 'no' );
			$hide_lightbox = get_option( 'wc_variation_images_hide_image_lightbox', 'no' );
			$hide_gallery  = get_option( 'wc_variation_images_hide_image_slider', 'no' );
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

	/**
	 * Upload variation images
	 *
	 * @param int         $loop Item loop.
	 * @param array       $variation_data Variation Data.
	 * @param \WC_Product $variation Variation Object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_variation_images_upload_images( $loop, $variation_data, $variation ) {
		$variation_id     = absint( $variation->ID );
		$variation_images = get_post_meta( $variation_id, 'wc_variation_images_variation_images', true );
		?>
		<div class="form-row form-row-full wc-variation-images-gallery-wrapper">
			<h4><?php esc_html_e( 'Variation Images', 'wc-variation-images' ); ?></h4>
			<div class="wc-variation-images-image-container">
				<ul id="wc-variation-images-image-list-<?php echo absint( $variation_id ); ?>"
					class="wc-variation-images-image-list">
					<?php
					if ( is_array( $variation_images ) && ! empty( $variation_images ) ) {
						foreach ( $variation_images as $image_id ) :
							$image_arr = wp_get_attachment_image_src( $image_id );
							?>
							<li class="wc-variation-images-image-info">
								<input type="hidden"
										name="wc_variation_images_image_variation_thumb[<?php echo esc_attr( $variation_id ); ?>][]"
										value="<?php echo esc_attr( $image_id ); ?>">
								<img src="<?php echo esc_url( $image_arr[0] ); ?>" alt="">
								<span class="wc-variation-images-remove-image dashicons dashicons-dismiss"></span>
							</li>
							<?php
						endforeach;
					}
					?>
				</ul>
			</div>
			<p class="wc-variation-images-add-container hide-if-no-js">
				<a href="#" data-wc_variation_images_variation_id="<?php echo absint( $variation->ID ); ?>"
					class="button wc-variation-images-add-image"><?php esc_html_e( 'Add Variation Images', 'wc-variation-images' ); ?></a>
			</p>
		</div>
		<?php
	}
}
