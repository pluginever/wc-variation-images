<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Variation_Images_Settings {

	private $settings_api;

	function __construct() {

		$this->settings_api = new \Ever_Settings_API();
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

	}

	function admin_init() {

		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		//initialize settings
		$this->settings_api->admin_init();
	}

	function get_settings_sections() {

		$sections = array(
			array(
				'id'    => 'wc_variation_images_general_settings',
				'title' => __( 'General Settings', 'wc-variation-images' )
			),
			array(
				'id'    => 'wc_variation_images_gallery_settings',
				'title' => __( 'Gallery Settings', 'wc-variation-images' ),
			),
		);

		return apply_filters( 'wc_variation_images_settings_sections', $sections );
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */

	function get_settings_fields() {

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
						'yes' => __( 'Yes', 'wc-variation-images' )
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
						'yes' => __( 'Yes', 'wc-variation-images' )
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
						'yes' => __( 'Yes', 'wc-variation-images' )
					)
				)
			),
			'wc_variation_images_gallery_settings' => array(
				array(
					'name'    => 'wc_variation_images_gallery_width',
					'label'   => __( 'Large Device Gallery Width', 'wc-variation-images' ),
					'desc'    => '<p class="description">' . __( '% For Large Devices. Slider Gallery Width in %. Default : 30. Limit: 10-100. ', 'wc-variation-images' ) . '</p>',
					'class'   => 'ever-field-inline',
					'type'    => 'number',
					'default' => 30,
					'min'     => 0,
					'max'     => 100
				),
				array(
					'name'    => 'wc_variation_images_gallery_medium_width',
					'label'   => __( 'Medium Device Gallery Width', 'wc-variation-images' ),
					'desc'    => '<p class="description">' . __( 'px For Medium Devices. Slider Gallery Width in pixel. Default : 0. Limit: 0-1000. ', 'wc-variation-images' ) . '</p>',
					'class'   => 'ever-field-inline',
					'type'    => 'number',
					'default' => 0,
					'min'     => 0,
					'max'     => 1000,
				),
				array(
					'name'    => 'wc_variation_images_gallery_small_width',
					'label'   => __( 'Small Device Gallery Width', 'wc-variation-images' ),
					'desc'    => '<p class="description">' . __( 'px For Small Devices. Slider Gallery Width in pixel. Default : 720. Limit: 0-1000. ', 'wc-variation-images' ) . '</p>',
					'class'   => 'ever-field-inline',
					'type'    => 'number',
					'default' => 720,
					'min'     => 0,
					'max'     => 1000,
				),
				array(
					'name'    => 'wc_variation_images_gallery_extra_small_width',
					'label'   => __( 'Extra Small Device Gallery Width', 'wc-variation-images' ),
					'desc'    => '<p class="description">' . __( 'px For Extra Small Devices. Slider Gallery Width in pixel. Default : 320. Limit: 0-1000. ', 'wc-variation-images' ) . '</p>',
					'class'   => 'ever-field-inline',
					'type'    => 'number',
					'default' => 320,
					'min'     => 0,
					'max'     => 1000,
				),


			)
		);

		return apply_filters( 'wc_variation_images_settings_fields', $settings_fields );
	}

	function admin_menu() {
		add_menu_page( __( 'Variation Image', 'wc-variation-images' ), __( 'Variation Image', 'wc-variation-images' ), 'manage_woocommerce', 'wc-variation-images', array(
			$this,
			'settings_page'
		), 'dashicons-images-alt2', '55.9' );

	}

	function settings_page() {

		echo '<div class="wrap">';
		echo sprintf( "<h2>%s</h2>", __( 'WC Variation Images Settings', 'wc-variation-images' ) );
		$this->settings_api->show_settings();
		echo '</div>';

	}

}

new WC_Variation_Images_Settings();
