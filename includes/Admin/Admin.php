<?php

namespace WooCommerceVariationImages\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 *
 * @since 1.0.0
 * @package WooCommerceVariationImages\Admin
 */
class Admin {
	/**
	 * Admin constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), PHP_INT_MAX );
		add_filter( 'update_footer', array( $this, 'update_footer' ), PHP_INT_MAX );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_footer', array( $this, 'admin_template_js' ) );
	}

	/**
	 * Add the plugin screens to the WooCommerce screens.
	 * This will load the WooCommerce admin styles and scripts.
	 *
	 * @param array $ids Screen ids.
	 *
	 * @return array
	 */
	public function screen_ids( $ids ) {
		return array_merge( $ids, self::get_screen_ids() );
	}

	/**
	 * Get screen ids.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_screen_ids() {
		$screen_ids = array(
			'woocommerce_page_wc-variation-images',
			'post.php',
			'post-new.php',
		);
		return apply_filters( 'wc_variation_images_screen_ids', $screen_ids );
	}

	/**
	 * Admin enqueue scripts.
	 *
	 * @param string $hook Hook name.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		$screen_ids = self::get_screen_ids();
		wp_enqueue_style( 'bytekit-components' );
		wp_enqueue_style( 'bytekit-layout' );

		wc_variation_images()->scripts->enqueue_style( 'wc-variation-image-halloween', 'css/halloween.css' );
		wc_variation_images()->scripts->register_style( 'wc-variation-images', 'css/admin.css' );
		wc_variation_images()->scripts->register_script( 'wc-variation-images', 'js/admin.js' );

		wp_localize_script(
			'wc-variation-images',
			'WC_VARIATION_IMAGES',
			array(
				'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
				'nonce'                      => wp_create_nonce( 'wc_variation_images' ),
				'is_pro_active'              => is_plugin_active( 'wc-variation-images-pro/wc-variation-images-pro.php' ),
				'variation_image_title'      => __( 'Variation Images', 'wc-variation-images' ),
				'add_variation_image_text'   => __( 'Add Additional Images', 'wc-variation-images' ),
				'admin_media_title'          => __( 'Variation Images', 'wc-variation-images' ),
				'admin_media_add_image_text' => __( 'Add to Variation', 'wc-variation-images' ),
				'media_upload_limit_text'    => __( 'Upload limit 3 images in free version', 'wc-variation-images' ),
				'admin_tip_message'          => __( 'Click on link below to add additional images. Click on image itself to remove the image. Click and drag image to re-order the image position.', 'wc-variation-images' ),
			)
		);

		if ( in_array( $hook, $screen_ids, true ) ) {
			wp_enqueue_style( 'wc-variation-images' );
			wp_enqueue_script( 'wc-variation-images' );
		}
	}

	/**
	 * Load html in admin footer.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_template_js() {
		require_once trailingslashit( WCVI_PLUGIN_TEMPLATES_DIR ) . 'wc-variation-images-variation-template.php';
	}

	/**
	 * Admin Menu
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Variation Images', 'wc-variation-images' ),
			__( 'Variation Images', 'wc-variation-images' ),
			'manage_options',
			'wc-variation-images',
			array( Settings::class, 'output' )
		);
	}

	/**
	 * Admin footer text.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.1.1
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( wc_variation_images()->get_review_url() && in_array( get_current_screen()->id, array( 'woocommerce_page_wc-variation-images' ), true ) ) {
			$footer_text = sprintf(
			/* translators: 1: Plugin name 2: WordPress */
				__( 'Thank you for using %1$s. If you like it, please leave us a %2$s rating. A huge thank you from PluginEver in advance!', 'wc-variation-images' ),
				'<strong>' . esc_html( wc_variation_images()->get_name() ) . '</strong>',
				'<a href="' . esc_url( wc_variation_images()->get_review_url() ) . '" target="_blank" class="wc-variation-images-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'wc-variation-images' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}

	/**
	 * Update footer.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.1.1
	 * @return string
	 */
	public function update_footer( $footer_text ) {
		if ( in_array( get_current_screen()->id, array( 'woocommerce_page_wc-variation-images' ), true ) ) {
			/* translators: 1: Plugin version */
			$footer_text = sprintf( esc_html__( 'Version %s', 'wc-variation-images' ), wc_variation_images()->get_version() );
		}

		return $footer_text;
	}
}
