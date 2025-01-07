<?php

namespace WooCommerceVariationImages\Admin;

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
						'title' => __( 'General settings', 'wc-variation-images' ),
						'type'  => 'title',
						'desc'  => __( 'The following options affect how the plugin will work.', 'wc-variation-images' ),
						'id'    => 'general_options',
					),
					array(
						'id'      => 'wc_variation_images_hide_image_zoom',
						'title'   => __( 'Hide Image Zoom', 'wc-variation-images' ),
						'desc'    => '<p class="description">' . __( 'Hide image zoom for variable product', 'wc-variation-images' ) . '</p>',
						'class'   => 'ever-field-inline',
						'type'    => 'select',
						'options' => array(
							'no'  => __( 'No', 'wc-variation-images' ),
							'yes' => __( 'Yes', 'wc-variation-images' ),
						),
					),
					array(
						'id'      => 'wc_variation_images_hide_image_lightbox',
						'title'   => __( 'Hide Lightbox', 'wc-variation-images' ),
						'desc'    => '<p class="description">' . __( 'Hide image lightbox for variable product', 'wc-variation-images' ) . '</p>',
						'class'   => 'ever-field-inline',
						'type'    => 'select',
						'options' => array(
							'no'  => __( 'No', 'wc-variation-images' ),
							'yes' => __( 'Yes', 'wc-variation-images' ),
						),
					),
					array(
						'id'      => 'wc_variation_images_hide_image_slider',
						'title'   => __( 'Hide Image Slider', 'wc-variation-images' ),
						'desc'    => '<p class="description">' . __( 'Hide image slider for variable product', 'wc-variation-images' ) . '</p>',
						'class'   => 'ever-field-inline',
						'type'    => 'select',
						'options' => array(
							'no'  => __( 'No', 'wc-variation-images' ),
							'yes' => __( 'Yes', 'wc-variation-images' ),
						),
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
