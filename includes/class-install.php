<?php

namespace Pluginever\WCVariationImages;

class Install {
    /**
     * Install constructor.
     */
    private function __construct() {
//        add_action( 'init', array( __CLASS__, 'install' ) );
//        add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
    }

    public static function install() {
        if ( get_option( 'wc_variation_images_install_date' ) ) {
            return;
        }

        if ( ! is_blog_installed() ) {
            return;
        }

        // Check if we are not already running this routine.
        if ( 'yes' === get_transient( 'wc_variation_imagess_installing' ) ) {
            return;
        }
        self::create_options();

    }

    /**
     * Save option data
     */
    private static function create_options() {
        //save db version
        update_option( 'wpcp_version', WPWVI_VERSION );

        //save install date
        update_option( 'wc_variation_imagess_install_date', current_time( 'timestamp' ) );
    }

}
