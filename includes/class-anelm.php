<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'ANELM' ) ) :

    /**
     * Main ANELM Class
     *
     * @class ANELM
     * @version 1.0.0
     */
    final class ANELM {

        /**
         * The single instance of the class.
         *
         * @var ANELM
         */
        protected static $instance = null;

        /**
         * Constructor for the class.
         */
        public function __construct() {
            $this->init_hooks();
        }

        
        /**
         * Initialize hooks and filters.
         */
        private function init_hooks() {
            // Register plugin activation hook
            register_activation_hook( ANELM_FILE, array( 'ANELM_install', 'install' ) );

            // Hook to install the plugin after plugins are loaded
            add_action( 'plugins_loaded', array( $this, 'anelm_install' ), 11 );
            add_action( 'anelm_init', array( $this, 'includes' ), 11 );
        }

        /**
         * Function to display admin notice if Elementor is not active.
         */
        public function admin_notice() {
            ?>
            <div class="error">
                <p><?php esc_html_e( 'Animate Elementor is enabled but not effective. It requires Elementor to work.', 'animate-elementor' ); ?></p>
            </div>
            <?php
        }

        /**
         * Function to initialize the plugin after Elementor is loaded.
         */
        public function anelm_install() {
            if ( ! did_action( 'elementor/loaded' ) ) {
                // Elementor is not active.
                add_action( 'admin_notices', array( $this, 'admin_notice' ) );
                return;
            }

            // Elementor is active. Proceed with initialization.
            do_action( 'anelm_init' );
        }

        /**
         * Main ANELM Instance.
         *
         * Ensures only one instance of ANELM is loaded or can be loaded.
         *
         * @static
         * @return ANELM - Main instance.
         */
        public static function instance() {
            if ( is_null( self::$instance ) ) :
                self::$instance         = new self();

                /**
                 * Fire a custom action to allow dependencies
                 * after the successful plugin setup
                 */
                do_action( 'anelm_plugin_loaded' );
            endif;
            return self::$instance;
        }

        /**
         * Include required files.
         *
         * @access private
         */
        public function includes() {
            /**
             * Core
             */
            include_once ANELM_PATH . 'includes/elementor/class-anelm-aos.php';

            if( is_admin() ) :
                $this->includes_admin();
            else :
                $this->includes_public();
            endif;
        }

        /**
         * Include Admin required files.
         *
         * @access private
         */
        public function includes_admin() {
            include_once ANELM_PATH . 'includes/class-anelm-install.php';
        }

        /**
         * Include Public required files.
         *
         * @access private
         */
        public function includes_public(){

        }


    }

endif;
