<?php

namespace PluginEver\VariationImages\Admin;

use PluginEver\VariationImages\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the deactivation survey.
 *
 * @since   1.0.0
 * @package PluginEver\VariationImages\Admin
 */
class Feedback extends Component {

	/**
	 * Whether to load.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function autoload(): bool {
		return is_admin();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_footer', array( $this, 'render_modal' ) );
		add_action( 'wp_ajax_wc_variation_images_feedback', array( $this, 'submit_feedback' ) );
	}

	/**
	 * Render the feedback modal.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_modal(): void {
		$screen = get_current_screen();

		if ( ! $screen || 'plugins' !== $screen->id ) {
			return;
		}

		$this->app->template->view(
			'admin.notices.feedback',
			array(
				'basename' => $this->app->basename(),
				'nonce'    => wp_create_nonce( 'wc_variation_images_feedback' ),
				'reasons'  => $this->get_reasons(),
			)
		);
	}

	/**
	 * Handle the feedback request.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function submit_feedback(): void {
		check_ajax_referer( 'wc_variation_images_feedback', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error();
		}

		$reason  = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_textarea_field( wp_unslash( $_POST['details'] ) ) : '';

		/**
		 * Filters the deactivation feedback endpoint. Return an empty string to skip sending.
		 *
		 * @since 1.0.0
		 * @param string $endpoint Remote endpoint URL.
		 */
		$endpoint = (string) $this->app->apply_filters( 'feedback_endpoint', '' );

		if ( '' !== $endpoint && '' !== $reason ) {
			wp_remote_post(
				$endpoint,
				array(
					'timeout'  => 5,
					'blocking' => false,
					'body'     => array(
						'plugin'  => 'wc-variation-images',
						'version' => $this->app->version,
						'reason'  => $reason,
						'details' => $details,
						'site'    => home_url(),
					),
				)
			);
		}

		wp_send_json_success();
	}

	/**
	 * Get the deactivation reasons.
	 *
	 * @since 1.0.0
	 * @return array<string, string> Reason labels keyed by slug.
	 */
	protected function get_reasons(): array {
		return array(
			'not_working'      => __( 'The plugin is not working', 'wc-variation-images' ),
			'found_better'     => __( 'I found a better plugin', 'wc-variation-images' ),
			'no_longer_needed' => __( 'I no longer need the plugin', 'wc-variation-images' ),
			'temporary'        => __( 'It is a temporary deactivation', 'wc-variation-images' ),
			'other'            => __( 'Other', 'wc-variation-images' ),
		);
	}
}
