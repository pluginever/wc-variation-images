<?php
/**
 * WC Variation Images Settings Class
 *
 *
 * @package     PluginEver\WC_Variation_Images
 * @since     1.0.0
 */

namespace PluginEver\WC_Variation_Images\Admin;

use \ByteEver\PluginFramework\v1_0_0 as Framework;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 *
 * @package PluginEver\WC_Variation_Images\Admin
 */
class Settings extends Framework\Admin\Settings {
	public function init() {
		add_action( 'admin_menu', [ $this, 'register_menu' ], 99 );
	}

	/**
	 * Get Settings.
	 *
	 * Register settings page.
	 *
	 * @since 1.0.0
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Variation Image', 'wc-variation-images' ),
			__( 'Variation Image', 'wc-variation-images' ),
			'manage_woocommerce',
			'wc-variation-images',
			array( $this, 'output_settings' ),
			'dashicons-images-alt2',
			'55.9'
		);
	}

	/**
	 * Get Settings.
	 *
	 * Return the settings page tabs, sections and fields.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_settings() {
		return array(
			'general' => array(
				'title'    => __( 'General', '' ),
				'sections' => array(
					'main' => array(

						'title'  => __( 'General', '' ),
						'fields' => array(
							array(
								'title'   => esc_html__( 'General Settings', '' ),
								'type'    => 'section',
								'tooltip' => esc_html__( 'The following options are for   globally.', '' ),
								'id'      => 'section_general_settings',
							),
							array(
								'title'   => esc_html__( 'Hide Image Zoom', 'wc-variation-images' ),
								'id'      => 'hide_image_zoom',
								'desc'    => esc_html__( 'Hide image zoom for variable product', 'wc-variation-images' ),
								'type'    => 'select',
								'options' => array(
									'no'  => __( 'No', 'wc-variation-images' ),
									'yes' => __( 'Yes', 'wc-variation-images' )
								),
							),
							array(
								'title'   => __( 'Hide Lightbox', 'wc-variation-images' ),
								'id'      => 'hide_image_lightbox',
								'desc'    => esc_html__( 'Hide image lightbox for variable product', 'wc-variation-images' ),
								'type'    => 'select',
								'options' => array(
									'no'  => __( 'No', 'wc-variation-images' ),
									'yes' => __( 'Yes', 'wc-variation-images' )
								),
							),
							array(
								'title'   => esc_html__( 'Hide Image Slider', 'wc-variation-images' ),
								'id'      => 'hide_image_slider',
								'desc'    => esc_html__( 'Hide image slider for variable product', 'wc-variation-images' ),
								'type'    => 'select',
								'options' => array(
									'no'  => __( 'No', 'wc-variation-images' ),
									'yes' => __( 'Yes', 'wc-variation-images' )
								)
							)

						)
					)
				)
			),
			'gallery' => array(
				'title'    => __( 'Gallery', '' ),
				'sections' => array(
					'main' => array(
						'title'  => __( 'Gallery', '' ),
						'fields' => array(
							array(
								'title'   => esc_html__( 'Gallery Settings', '' ),
								'type'    => 'section',
								'tooltip' => esc_html__( 'The following options are for gallery settings.', '' ),
								'id'      => 'section_gallery_settings',
							),
							array(
								'title'   => esc_html__( 'Large Device Gallery Width', 'wc-variation-images' ),
								'id'      => 'gallery_width',
								'desc'    => esc_html__( '% For Large Devices. Slider Gallery Width in %. Default : 30. Limit: 10-100. ', 'wc-variation-images' ),
								'type'    => 'number',
								'default' => 30,
								'min'     => 0,
								'max'     => 100
							),
							array(
								'title'   => esc_html__( 'Medium Device Gallery Width', 'wc-variation-images' ),
								'id'    => 'gallery_medium_width',
								'desc'    => esc_html__( 'px For Medium Devices. Slider Gallery Width in pixel. Default : 0. Limit: 0-1000. ', 'wc-variation-images' ),
								'type'    => 'number',
								'default' => 0,
								'min'     => 0,
								'max'     => 1000,
							),
							array(
								'title'   => esc_html__( 'Small Device Gallery Width', 'wc-variation-images' ),
								'id'    => 'gallery_small_width',
								'desc'    => esc_html__( 'px For Small Devices. Slider Gallery Width in pixel. Default : 720. Limit: 0-1000. ', 'wc-variation-images' ),
								'type'    => 'number',
								'default' => 720,
								'min'     => 0,
								'max'     => 1000,
							),
							array(
								'title'   => esc_html__( 'Extra Small Device Gallery Width', 'wc-variation-images' ),
								'id'    => 'gallery_extra_small_width',
								'desc'    => esc_html__( 'px For Extra Small Devices. Slider Gallery Width in pixel. Default : 320. Limit: 0-1000. ', 'wc-variation-images' ),
								'type'    => 'number',
								'default' => 320,
								'min'     => 0,
								'max'     => 1000,
							)
						),
					),
				)
			),
		);
	}
}

