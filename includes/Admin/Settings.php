<?php

namespace WooCommerceVariationImages\Admin;

use WooCommerceVariationImages\Controllers\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings.
 *
 * @since   1.0.0
 * @package WooCommerceVariationImages\Admin
 */
class Settings extends \WooCommerceVariationImages\ByteKit\Admin\Settings {

	/**
	 * Get settings tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_tabs() {
		$tabs = array(
			'general' => __( 'General', 'wc-variation-images' ),
		);

		return apply_filters( 'wc_variation_images_settings_tabs', $tabs );
	}

	/**
	 * Get settings.
	 *
	 * @param string $tab Current tab.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings( $tab ) {
		$settings = array();
		switch ( $tab ) {
			case 'general':
				$settings = array(
					array(
						'title' => __( 'Gallery settings', 'wc-variation-images' ),
						'type'  => 'title',
						'desc'  => __( 'The following options affect how the plugin will work.', 'wc-variation-images' ),
						'id'    => 'general_options',
					),
					array(
						'id'       => 'wcvi_disable_image_zoom',
						'title'    => __( 'Disable Image Zoom', 'wc-variation-images' ),
						'desc'     => __( 'Disable image zoom for variable product.', 'wc-variation-images' ),
						'desc_tip' => __( 'Check this box to disable the image zoom effect on hover for this product.', 'wc-variation-images' ),
						'type'     => 'select',
						'options'  => array(
							'no'  => __( 'No', 'wc-variation-images' ),
							'yes' => __( 'Yes', 'wc-variation-images' ),
						),
						'default'  => 'no',
					),
					array(
						'id'       => 'wcvi_disable_image_lightbox',
						'title'    => __( 'Disable Lightbox', 'wc-variation-images' ),
						'desc'     => __( 'Disable image lightbox for variable product.', 'wc-variation-images' ),
						'desc_tip' => __( 'Enable this option to hide the lightbox on the product page.', 'wc-variation-images' ),
						'type'     => 'select',
						'options'  => array(
							'no'  => __( 'No', 'wc-variation-images' ),
							'yes' => __( 'Yes', 'wc-variation-images' ),
						),
						'default'  => 'no',
					),
					array(
						'id'       => 'wcvi_disable_image_slider',
						'title'    => __( 'Disable Image Slider', 'wc-variation-images' ),
						'desc'     => __( 'Disable image slider for variable product.', 'wc-variation-images' ),
						'desc_tip' => __( 'Enable this option to hide the image slider for this specific variation on the frontend.', 'wc-variation-images' ),
						'type'     => 'select',
						'options'  => array(
							'no'  => __( 'No', 'wc-variation-images' ),
							'yes' => __( 'Yes', 'wc-variation-images' ),
						),
						'default'  => 'no',
					),
					array(
						'id'       => 'wcvi_gallery_position',
						'title'    => __( 'Gallery Position', 'wc-variation-images' ),
						'desc'     => __( 'Set product image position.', 'wc-variation-images' ),
						'desc_tip' => __( 'Select the position of the product gallery on the product page.', 'wc-variation-images' ),
						'type'     => 'select',
						'options'  => Helpers::gallery_position_list(),
						'default'  => 'bottom',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'general_options',
					),
				);
				break;
		}
		return apply_filters( 'wc_variation_images_get_settings_' . $tab, $settings );
	}

	/**
	 * Output settings form.
	 *
	 * @param array $settings Settings.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function output_form( $settings ) {
		$current_tab = $this->get_current_tab();
		/**
		 * Action hook to output settings form.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_variation_images_settings_' . $current_tab );
		parent::output_form( $settings );
	}

	/**
	 * Output tabs.
	 *
	 * @param array $tabs Tab.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_tabs( $tabs ) {
		parent::output_tabs( $tabs );
		if ( wc_variation_images()->get_docs_url() ) {
			printf( '<a href="%s" class="nav-tab" target="_blank">%s</a>', esc_url( wc_variation_images()->get_docs_url() ), esc_html__( 'Documentation', 'wc-variation-images' ) );
		}
	}
}
