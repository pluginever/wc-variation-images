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
	 * Settings Api.
	 *
	 * @var $settings_api
	 */
	private $settings_api;

	/**
	 * Admin constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->settings_api = new SettingsAPI();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_handler' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'woocommerce_save_product_variation', array( $this, 'wc_variation_images_save_product_variation' ), 10, 1 );
		add_action( 'admin_footer', array( $this, 'admin_template_js' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), PHP_INT_MAX );
		add_filter( 'update_footer', array( $this, 'update_footer' ), PHP_INT_MAX );
	}

	/**
	 * Admin enqueue scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_scripts_handler() {
		wc_variation_images()->scripts->register_style( 'wc-variation-images-halloween', 'css/halloween.css' );
		wp_enqueue_style( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . 'css/admin.css', array(), WC_VARIATION_IMAGES_VERSION );
		wp_register_script( 'wc-variation-images', WC_VARIATION_IMAGES_ASSETS_URL . 'js/admin.js', array( 'jquery' ), WC_VARIATION_IMAGES_VERSION, true );
		wp_localize_script(
			'wc-variation-images',
			'WC_VARIATION_IMAGES',
			array(
				'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
				'nonce'                      => wp_create_nonce( 'wc_variation_images' ),
				'variation_image_title'      => __( 'Variation Images', 'wc-variation-images' ),
				'add_variation_image_text'   => __( 'Add Additional Images', 'wc-variation-images' ),
				'admin_media_title'          => __( 'Variation Images', 'wc-variation-images' ),
				'admin_media_add_image_text' => __( 'Add to Variation', 'wc-variation-images' ),
				'admin_tip_message'          => __( 'Click on link below to add additional images. Click on image itself to remove the image. Click and drag image to re-order the image position.', 'wc-variation-images' ),
			)
		);
		wp_enqueue_script( 'wc-variation-images' );
	}

	/**
	 * Load html in admin footer.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_template_js() {
		require_once trailingslashit( WC_VARIATION_IMAGES_TEMPLATES_DIR ) . 'wc-variation-images-variation-template.php';
	}

	/**
	 * Admin init function.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_init() {
		// Set the settings.
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		// Initialize settings.
		$this->settings_api->admin_init();
	}

	/**
	 * Get settings section.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings_sections() {
		$sections = array(
			array(
				'id'    => 'wc_variation_images_general_settings',
				'title' => __( 'General Settings', 'wc-variation-images' ),
			),
		);

		return apply_filters( 'wc_variation_images_settings_sections', $sections );
	}

	/**
	 * Returns all the settings fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings_fields() {
		$settings_fields = array(
			'wc_variation_images_general_settings' => array(
				array(
					'name'    => 'wc_variation_images_hide_image_zoom',
					'label'   => __( 'Hide Image Zoom', 'wc-variation-images' ),
					'desc'    => '<p class="description">' . __( 'Hide image zoom for variable product', 'wc-variation-images' ) . '</p>',
					'class'   => 'ever-field-inline',
					'type'    => 'select',
					'options' => array(
						'no'  => __( 'No', 'wc-variation-images' ),
						'yes' => __( 'Yes', 'wc-variation-images' ),
					),
				),
				array(
					'name'    => 'wc_variation_images_hide_image_lightbox',
					'label'   => __( 'Hide Lightbox', 'wc-variation-images' ),
					'desc'    => '<p class="description">' . __( 'Hide image lightbox for variable product', 'wc-variation-images' ) . '</p>',
					'class'   => 'ever-field-inline',
					'type'    => 'select',
					'options' => array(
						'no'  => __( 'No', 'wc-variation-images' ),
						'yes' => __( 'Yes', 'wc-variation-images' ),
					),
				),
				array(
					'name'    => 'wc_variation_images_hide_image_slider',
					'label'   => __( 'Hide Image Slider', 'wc-variation-images' ),
					'desc'    => '<p class="description">' . __( 'Hide image slider for variable product', 'wc-variation-images' ) . '</p>',
					'class'   => 'ever-field-inline',
					'type'    => 'select',
					'options' => array(
						'no'  => __( 'No', 'wc-variation-images' ),
						'yes' => __( 'Yes', 'wc-variation-images' ),
					),
				),
			),
		);

		return apply_filters( 'wc_variation_images_settings_fields', $settings_fields );
	}

	/**
	 * Admin Menu
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Variation Image', 'wc-variation-images' ),
			__( 'Variation Image', 'wc-variation-images' ),
			'manage_woocommerce',
			'wc-variation-images',
			array(
				$this,
				'settings_page',
			),
			'dashicons-images-alt2',
			'55.9'
		);
	}

	/**
	 * Settings page text.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function settings_page() {
		echo '<div class="wrap">';
		printf( '<h2>%s</h2>', esc_html__( 'WC Variation Images Settings', 'wc-variation-images' ) );
		$this->settings_api->show_settings();
		echo '</div>';
	}

	/**
	 * Save Variation Product.
	 *
	 * @param int $variation_id Variation ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_variation_images_save_product_variation( $variation_id ) {
		$attachment_ids = array();
		if ( isset( $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] ) ) {
			// Sanitize.
			$attachment_ids = array_map( 'absint', $_POST['wc_variation_images_image_variation_thumb'][ $variation_id ] );
			// Filter.
			$attachment_ids = array_filter( $attachment_ids );
			// Unique.
			$attachment_ids = array_unique( $attachment_ids );
		}
		update_post_meta( $variation_id, 'wc_variation_images_variation_images', $attachment_ids );
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
		if ( wc_variation_images()->get_review_url() && in_array( get_current_screen()->id, array( 'toplevel_page_wc-variation-images' ), true ) ) {
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
		if ( in_array( get_current_screen()->id, array( 'toplevel_page_wc-variation-images' ), true ) ) {
			/* translators: 1: Plugin version */
			$footer_text = sprintf( esc_html__( 'Version %s', 'wc-variation-images' ), wc_variation_images()->get_version() );
		}

		return $footer_text;
	}
}
