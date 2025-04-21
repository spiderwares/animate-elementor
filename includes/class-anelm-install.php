<?php

/**
 * Installation related functions and actions.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'ANELM_install' ) ) :

    /**
     * ANELM_install Class
     *
     * Handles installation processes like creating database tables,
     * setting up roles, and creating necessary pages on plugin activation.
     */
    class ANELM_install {

        /**
         * Hook into WordPress actions and filters.
         */
        public static function init() {
            add_filter( 'plugin_action_links_' . ANELM_BASENAME, array( __CLASS__, 'plugin_action_links' ), 10, 1 );
        }

        /**
         * Add plugin action links.
         *
         * @param array $links Array of action links.
         * @return array Modified array of action links.
         */
        public static function plugin_action_links( $links ) {
            $action_links = array(
                'hire_us' => sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url( 'https://wordpress.org/support/users/harshilitaliya/' ),
                    esc_attr__( 'Hire Us', 'animate-elementor' ),
                    esc_html__( 'Hire Us', 'animate-elementor' )
                ),
                'request_feature' => sprintf(
                    '<a href="%s" aria-label="%s">%s</a>',
                    esc_url( 'https://wordpress.org/support/users/harshilitaliya/' ),
                    esc_attr__( 'Request a custom animation', 'animate-elementor' ),
                    esc_html__( 'Request a custom animation', 'animate-elementor' )
                ),
            );
            return array_merge( $action_links, $links );
        }

    }

    // Initialize the installation process
    ANELM_install::init();

endif;
