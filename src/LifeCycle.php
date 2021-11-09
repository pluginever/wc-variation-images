<?php
/**
 * WC Variation Images LifeCycle class
 *
 *
 * @package     PluginEver\WC_Variation_Images
 * @since     1.0.0
 */

namespace PluginEver\WC_Variation_Images;

use \ByteEver\PluginFramework\v1_0_0 as Framework;

defined( 'ABSPATH' ) || exit;

/**
 * Class Lifecycle
 *
 * @package PluginEver\WC_Variation_Images
 * @since 1.0.0
*/
class LifeCycle extends Framework\Lifecycle {
	/**
	 * Updates and callbacks that need to be run per version.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $updates = array();
}
