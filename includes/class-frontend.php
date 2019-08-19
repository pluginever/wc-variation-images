<?php

namespace Pluginever\WCVariationImages;

class Frontend {
	/**
	 * The single instance of the class.
	 *
	 * @var Frontend
	 * @since 1.0.0
	 */
	protected static $init = null;

	/**
	 * Frontend Instance.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Frontend - Main instance.
	 */
	public static function init() {
		if ( is_null( self::$init ) ) {
			self::$init = new self();
			self::$init->setup();
		}

		return self::$init;
	}

	/**
	 * Initialize all frontend related stuff
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup() {
		$this->includes();
		$this->init_hooks();
		$this->instance();
	}

	/**
	 * Includes all frontend related files
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function includes() {
		require_once dirname( __FILE__ ) . '/template-functions.php';
		require_once dirname( __FILE__ ) . '/class-shortcode.php';
	}

	/**
	 * Register all frontend related hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_hooks() {

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'init', array($this, 'remove_woocommerce_default_template' ), 200);
		add_action( 'woocommerce_before_single_product_summary', array($this,'woocommerce_override_product_images'), 22);
		//add_filter('woocommerce_available_variation', array($this, 'add_aditional_variation_image'), 90, 3);
	}

	/**
	 * Fire off all the instances
	 *
	 * @since 1.0.0
	 */
	protected function instance() {
		new ShortCode();
	}

	/**
	 * Loads all frontend scripts/styles
	 *
	 * @param $hook
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {

		$js_dir     = WPWVI_ASSETS_URL . '/js/';
		$css_dir    = WPWVI_ASSETS_URL . '/css/';
		$vendor_dir = WPWVI_ASSETS_URL . '/vendor/';

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_style('jquery-slick', $vendor_dir . "slick/slick{$suffix}.css", [], WPWVI_VERSION);
		wp_enqueue_style('jquery-slick');

		wp_register_script('jquery-slick', $vendor_dir . "slick/slick{$suffix}.js", ['jquery'], WPWVI_VERSION, true);
		wp_enqueue_script('jquery-slick');

		wp_register_script('wc-variation-images', $js_dir . "frontend/frontend{$suffix}.js", ['jquery'], WPWVI_VERSION, true);
		wp_localize_script('wc-variation-images', 'wpwvi', ['ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce('wc_variation_images')]);

		wp_register_style('wc-variation-images', $css_dir . "frontend{$suffix}.css", [], WPWVI_VERSION);
		wp_enqueue_style('wc-variation-images');

		wp_enqueue_script('wc-variation-images');
	}

	/**
	 * remove woocommerce default template
	 * since 1.0.0
	 */
	public function remove_woocommerce_default_template(){

		remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
	}

	public function woocommerce_override_product_images(){

		require_once trailingslashit(WPWVI_TEMPLATES_DIR) . "wpwvi-product-images.php";
	}

	public function add_aditional_variation_image($available_variation, $variationProductObject, $variation) {

		$product_id = absint($variation->get_parent_id());
		$variation_id = absint($variation->get_id());
		$variation_image_id = absint($variation->get_image_id());
		$has_variation_gallery_images = (bool)get_post_meta($variation_id, 'wpwvi_variation_images', true);
		$product = wc_get_product($product_id);

		if ($has_variation_gallery_images) {
			$gallery_images = (array)get_post_meta($variation_id, 'wpwvi_variation_images', true);
		} else {
			$gallery_images = $product->get_gallery_image_ids();
		}


		if ($variation_image_id) {

			array_unshift($gallery_images, $variation->get_image_id());
		} else {
			if (has_post_thumbnail($product_id)) {
				array_unshift($gallery_images, get_post_thumbnail_id($product_id));
			}
		}

		$extra_variation['wpwvi_variation_images'] = array();

		foreach ($gallery_images as $i => $image_id) {

			$extra_variation['wpwvi_variation_images'][$i] = wc_get_product_attachment_props($image_id);
			$extra_variation['wpwvi_variation_images'][$i]['image_id'] = $image_id;
			$extra_variation['wpwvi_variation_images'][$i]['css_class'] = ($i < 1) ? 'wp-post-image' : '';

		}
		return  $extra_variation;
	}
}

Frontend::init();
