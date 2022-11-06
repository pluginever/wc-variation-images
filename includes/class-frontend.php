<?php

namespace WC_Variation_Images;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Frontend.
 *
 * Handles frontend functionality.
 *
 * @since 1.0.0
 * @package WC_Variation_Images
 */
class Frontend extends Controller {

	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		$this->get_plugin()->register_style( 'wc-variation-images-frontend', 'css/frontend.css' );
		$this->get_plugin()->register_script( 'wc-variation-images-frontend', 'js/frontend.js' );

		// todo enqueue scripts.
	}

}
