<?php

namespace WooCommerceVariationImages\ByteKit\Admin;

use WooCommerceVariationImages\ByteKit\Plugin;

defined( 'ABSPATH' ) || exit();

/**
 * Admin Notice Handler Class.
 *
 * @since   1.0.0
 * @version 1.0.2
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package ByteKit/Plugin
 * @license GPL-3.0+
 */
class Notices {
	/**
	 * The plugin instance.
	 *
	 * @since 1.0.0
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * The notices.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $notices = array();

	/**
	 * Construct and initialize the service trait.
	 *
	 * @param Plugin $plugin The plugin instance.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		add_action( 'wp_ajax_' . $this->plugin->get_prefix() . '_dismiss_notice', array( $this, 'ajax_dismiss_notice' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Dismisses the notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_dismiss_notice() {
		if ( ! check_ajax_referer( $this->plugin->get_prefix() . '_dismiss_notice', 'nonce', false ) || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
			exit;
		}
		$notice_id   = isset( $_POST['notice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_id'] ) ) : '';
		$snooze      = isset( $_POST['snooze'] ) ? filter_var( wp_unslash( $_POST['snooze'] ), FILTER_VALIDATE_BOOLEAN ) : false;
		$snooze_time = isset( $_POST['snooze_time'] ) ? absint( wp_unslash( $_POST['snooze_time'] ) ) : 7 * DAY_IN_SECONDS;
		$notice      = array_key_exists( $notice_id, $this->notices ) ? $this->notices[ $notice_id ] : null;
		if ( ! is_null( $notice ) ) {
			if ( $snooze ) {
				$this->snooze( $notice_id, $snooze_time );
			} else {
				$this->dismiss( $notice_id );
			}
			wp_cache_flush();
			wp_send_json_success();
			exit;
		}
		wp_send_json_error();
		exit;
	}

	/**
	 * Display the admin notices.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_notices() {
		if ( empty( $this->notices ) ) {
			return;
		}

		// Enqueue the notices script.
		wp_enqueue_script( 'bytekit-admin' );

		foreach ( $this->notices as $notice ) {
			if ( $this->should_display( $notice ) ) {
				$classes = array_unique( array_filter( wp_parse_list( $notice['class'] ) ) );
				$style   = ! empty( $notice['style'] ) ? $notice['style'] : '';
				$message = $notice['message'];
				if ( str_ends_with( $message, '.php' ) ) {
					wp_enqueue_style( 'bytekit-components' );
					$path = wp_normalize_path( $message );
					if ( file_exists( $path ) ) {
						ob_start();
						include $path;
						$message = ob_get_clean();
					}
				}

				// if dismissible then add is-dismissible class.
				if ( $notice['dismissible'] ) {
					$classes[] = 'is-dismissible';
				}

				// if empty message then skip.
				if ( empty( $message ) ) {
					continue;
				}

				// if message does not contain html tags then wrap it with a paragraph.
				if ( ! preg_match( '/<[^>]+>/', $message ) ) {
					$message = wpautop( $message );
				}

				printf(
					'<div class="notice bk-notice notice-%1$s %2$s" data-notice_id="%3$s" data-nonce="%4$s" data-action="%5$s" style="%6$s">%7$s%8$s</div>',
					esc_attr( $notice['type'] ),
					esc_attr( implode( ' ', $classes ) ),
					esc_attr( $notice['notice_id'] ),
					esc_attr( wp_create_nonce( $this->plugin->get_prefix() . '_dismiss_notice' ) ),
					esc_attr( $this->plugin->get_prefix() . '_dismiss_notice' ),
					esc_attr( $style ),
					wp_kses_post( wptexturize( $message ) ),
					$notice['dismissible'] ? '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice', 'wc-variation-images' ) . '</span></button>' : ''
				);
			}
		}
	}

	/**
	 * Add a notice.
	 *
	 * @param string|array $args The notice arguments or message.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add( $args ) {
		if ( is_string( $args ) ) {
			$args = array( 'message' => $args );
		}
		$args = wp_parse_args(
			$args,
			array(
				'message'     => '',
				'type'        => 'info',
				'dismissible' => true,
				'capability'  => 'manage_options',
				'notice_id'   => '',
				'class'       => '',
				'style'       => '',
			)
		);

		// if message is empty then skip.
		if ( empty( $args['message'] ) ) {
			return;
		}

		// if we do not have a notice id then generate one.
		if ( empty( $args['notice_id'] ) ) {
			$args['notice_id'] = $this->plugin->get_prefix() . '_' . md5( $args['message'] . $args['type'] );
		}

		// if dismissible and already dismissed, don't add the notice.
		if ( true === filter_var( $args['dismissible'], FILTER_VALIDATE_BOOLEAN ) && $this->is_dismissed( $args['notice_id'] ) ) {
			return;
		}

		$this->notices[ $args['notice_id'] ] = $args;
	}

	/**
	 * Get the notices.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_notices() {
		return $this->notices;
	}

	/**
	 * Is the notice dismissed?
	 *
	 * @param string $id The notice id.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_dismissed( $id ) {
		if ( 'yes' === get_option( $id ) || 'yes' === get_option( '_transient_' . $id ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Should the notice be displayed?
	 *
	 * @param array $notice The notice options.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function should_display( $notice ) {
		if ( ( $notice['notice_id'] && $this->is_dismissed( $notice['notice_id'] ) ) || ( $notice['capability'] && ! current_user_can( $notice['capability'] ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Dismiss a notice.
	 *
	 * @param string $id The notice id.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function dismiss( $id ) {
		update_option( $id, 'yes' );
		delete_transient( $id );
	}

	/**
	 * Snooze a notice.
	 *
	 * @param string $id The notice id.
	 * @param int    $time The time to snooze the notice for.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function snooze( $id, $time = null ) {
		set_transient( $id, 'yes', absint( $time ) );
	}
}
