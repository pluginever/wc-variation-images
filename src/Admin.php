<?php
/**
 * WC Variation Images Main Admin File
 *
 *
 * @package     PluginEver\WC_Variation_Images
 * @since     1.0.0
 */

namespace PluginEver\WC_Variation_Images;

use \ByteEver\PluginFramework\v1_0_0 as Framework;
use PluginEver\WC_Variation_Images\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin
 *
 * @package PluginEver\WC_Variation_Images
*/
class Admin {

	/**
	 * Binds and sets up implementations.
	 */
	public function init(){
		wc_variation_images()->register_service( Settings::class, $this );
	}
}
