<?php

namespace WooCommerceVariationImages\ByteKit\Admin;

/**
 * Settings class
 *
 * @since   1.0.0
 * @version 1.0.2
 * @author  Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @package ByteKit/Settings
 * @license GPL-3.0+
 */
abstract class Settings {
	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Init settings.
	 *
	 * @since 1.0.0
	 * @return self
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Settings constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'buffer_start' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
	}


	/**
	 * Get settings tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	abstract public function get_tabs();

	/**
	 * Get settings.
	 *
	 * @param string $tab Tab name.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	abstract public function get_settings( $tab );

	/**
	 * Buffer start.
	 *
	 * @since 1.0.0
	 */
	public function buffer_start() {
		ob_start();
	}

	/**
	 * Save settings.
	 *
	 * @since 1.0.0
	 * @return bool True if saved, false otherwise.
	 */
	public function save_settings() {
		$class_name = get_called_class();
		if ( empty( $_POST ) || ! isset( $_POST[ $class_name ] ) ) {
			return false;
		}
		check_admin_referer( $class_name );
		$current_tab = $this->get_current_tab();
		$settings    = $this->get_settings( $current_tab );
		if ( $this->save_fields( $settings ) ) {
			add_settings_error( $class_name, 'response', __( 'Settings saved.', 'bytekit-textdomain' ), 'updated' );
			return true;
		}

		return false;
	}

	/**
	 * Register scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_scripts() {
		$basename = plugin_basename( __FILE__ );
		$version  = filemtime( plugin_dir_path( __DIR__ ) . 'assets/js/settings.js' );
		wp_register_script( $basename, plugins_url( 'assets/js/settings.js', __DIR__ ), array( 'jquery' ), $version, true );
	}

	/**
	 * Output settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_settings() {
		$tabs        = $this->get_tabs();
		$current_tab = $this->get_current_tab();
		$tab_exists  = isset( $tabs[ $current_tab ] );
		$settings    = $this->get_settings( $current_tab );
		if ( ! empty( $tabs ) && ! $tab_exists && ! headers_sent() ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . $this->get_current_page() ) );
			exit();
		}
		// Enqueue scripts.
		wp_enqueue_script( plugin_basename( __FILE__ ) );
		?>
		<div class="wrap bk-wrap woocommerce">
			<nav class="nav-tab-wrapper bk-navbar">
				<?php $this->output_tabs( $tabs ); ?>
			</nav>
			<hr class="wp-header-end">
			<div class="bk-poststuff">
				<div class="column-1">
					<?php $this->output_form( $settings ); ?>
				</div>
				<div class="column-2">
					<?php $this->output_widgets(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Output tabs.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_tabs( $tabs ) {
		foreach ( $tabs as $tab_id => $tab_name ) {
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->get_current_page() . '&tab=' . $tab_id ) ); ?>" class="nav-tab <?php echo esc_attr( $this->get_current_tab() === $tab_id ? 'nav-tab-active' : '' ); ?>">
				<?php echo esc_html( $tab_name ); ?>
			</a>
			<?php
		}
	}

	/**
	 * Output settings form.
	 *
	 * @param array $settings Settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_form( $settings ) {
		if ( ! empty( $settings ) ) {
			$class_name = get_called_class();
			settings_errors( $class_name );
			?>
			<form method="post" id="mainform" action="" enctype="multipart/form-data">
				<?php $this->output_fields( $settings ); ?>
				<?php wp_nonce_field( $class_name ); ?>
				<?php submit_button( null, 'primary', $class_name ); ?>
			</form>
			<?php
		}
	}

	/**
	 * Output admin fields.
	 *
	 * Loops through the woocommerce options array and outputs each field.
	 *
	 * @param array[] $options Opens array to output.
	 */
	public function output_fields( $options ) {
		if ( function_exists( 'woocommerce_admin_fields' ) ) {
			woocommerce_admin_fields( $options );
		}
	}

	/**
	 * Save admin fields.
	 *
	 * Loops through the options array and outputs each field.
	 *
	 * @param array $options Options array to output.
	 * @param array $data    Optional. Data to use for saving. Defaults to $_POST.
	 * @return bool
	 */
	public function save_fields( $options, $data = null ) {
		if ( class_exists( '\WC_Admin_Settings' ) && ! empty( $options ) && \WC_Admin_Settings::save_fields( $options, $data ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Output settings sidebar.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_widgets() {
		$this->output_premium_widget();
		$this->output_plugins_widget();
		$this->output_support_widget();
	}

	/**
	 * Output premium widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_premium_widget() {
		// Premium widget.
	}

	/**
	 * Output promo plugins.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_plugins_widget() {
		$promo_plugins = $this->get_promo_plugins();
		if ( ! empty( $promo_plugins ) ) {
			$installed = get_plugins();
			foreach ( $promo_plugins as $promo_plugin ) {
				$promo_plugin = wp_parse_args(
					$promo_plugin,
					array(
						'name'        => '',
						'description' => '',
						'basename'    => '',
						'slug'        => '',
						'badge'       => esc_html__( 'Recommended', 'bytekit-textdomain' ),
						'button'      => esc_html__( 'Install Now', 'bytekit-textdomain' ),
						'installed'   => false,
					)
				);
				// If basename or slug is not set, skip.
				if ( empty( $promo_plugin['basename'] ) && empty( $promo_plugin['slug'] ) ) {
					continue;
				}
				if ( ! empty( $promo_plugin['basename'] ) ) {
					$basename = $promo_plugin['basename'];
				} else {
					$basename = $promo_plugin['slug'] . '/' . $promo_plugin['slug'] . '.php';
				}
				if ( isset( $installed[ $basename ] ) ) {
					continue;
				}
				// get file name from basename.
				$basename_parts = explode( '/', $basename );
				$slug           = current( $basename_parts );
				$install_url    = add_query_arg(
					array(
						'action' => 'install-plugin',
						'plugin' => $slug,
					),
					network_admin_url( 'update.php' )
				);
				$install_url    = wp_nonce_url( $install_url, 'install-plugin_' . $slug );
				?>
				<div class="bk-card">
					<div class="bk-card__header">
						<?php echo esc_html( $promo_plugin['name'] ); ?>
					</div>
					<div class="bk-card__body">
						<?php echo wp_kses_post( wpautop( $promo_plugin['description'] ) ); ?>
					</div>
					<div class="bk-card__footer">
						<?php if ( ! empty( $promo_plugin['badge'] ) ) : ?>
							<span class="bk-badge bk-badge--primary">
								<?php echo esc_html( $promo_plugin['badge'] ); ?>
							</span>
						<?php endif; ?>
						<a href="<?php echo esc_url( $install_url ); ?>" target="_blank" class="bk-button bk-button--secondary">
							<?php echo esc_html( $promo_plugin['button'] ); ?>
						</a>
					</div>
				</div>
				<?php
			}
		}
	}

	/**
	 * Output sidebar links.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_support_widget() {
		$support_links = $this->get_support_links();
		if ( ! empty( $support_links ) ) {
			?>
			<div class="bk-card">
				<div class="bk-card__header">
					<?php esc_html_e( 'Need Help?', 'bytekit-textdomain' ); ?>
				</div>
				<div class="bk-card__body">
					<ul>
						<?php foreach ( $support_links as $key => $link ) : ?>
							<li>
								<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank">
									<?php echo esc_html( $link['label'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Get promo plugins.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_promo_plugins() {
		return array(
			array(
				'name'        => 'WC Min Max Quantities',
				'slug'        => 'wc-min-max-quantities',
				'description' => 'Set minimum and maximum price or quantity for WooCommerce products.',
				'link'        => 'https://wordpress.org/plugins/wc-min-max-quantities/',
				'badge'       => esc_html__( 'Recommended', 'bytekit-textdomain' ),
				'button'      => esc_html__( 'Install Now', 'bytekit-textdomain' ),
			),
		);
	}

	/**
	 * Get support links.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_support_links() {
		return array(
			'facebook'        => array(
				'label' => __( 'Join our Community', 'bytekit-textdomain' ),
				'url'   => 'https://www.facebook.com/groups/pluginever',
			),
			'feature-request' => array(
				'label' => __( 'Request a Feature', 'bytekit-textdomain' ),
				'url'   => 'https://www.pluginever.com/contact/',
			),
			'bug-report'      => array(
				'label' => __( 'Report a Bug', 'bytekit-textdomain' ),
				'url'   => 'https://www.pluginever.com/contact/',
			),
		);
	}

	/**
	 * Get current page.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_current_page() {
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
		return ! empty( $page ) ? sanitize_text_field( wp_unslash( $page ) ) : '';
	}

	/**
	 * Get the current tab.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_current_tab() {
		$tabs = $this->get_tabs();
		$tab  = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );
		$tab  = ! empty( $tab ) ? sanitize_text_field( wp_unslash( $tab ) ) : '';

		if ( ! array_key_exists( $tab, $tabs ) ) {
			$tab = key( $tabs );
		}

		return $tab;
	}

	/**
	 * Save default settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_defaults() {
		$tabs = $this->get_tabs();
		foreach ( $tabs as $tab => $label ) {
			$options = $this->get_settings( $tab );

			foreach ( $options as $option ) {
				if ( isset( $option['default'] ) && isset( $option['id'] ) ) {
					$autoload = isset( $option['autoload'] ) ? (bool) $option['autoload'] : true;
					add_option( $option['id'], $option['default'], '', $autoload );
				}
			}
		}
	}

	/**
	 * Output settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function output() {
		self::instance()->output_settings();
	}
}
