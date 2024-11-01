<?php
/**
 * The DFM class. Defines the necessary constants 
 * and includes the necessary files for theme's operation.
 *
 * @package DFM
 * @subpackage Pamy
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if ( ! class_exists( 'DFM_Dropshippers' ) ) :
    /**
     * 
     */
    final class DFM_Dropshippers {
        public $options;
        /**
         * Constructor calls the init method
         *
         * @since 1.0
         */
        public function __construct( $options = array() ) {
            add_action( 'admin_menu', 'dfm_dropshipeprs_admin_menu_init' );
            function dfm_dropshipeprs_admin_menu_init() {
                add_menu_page( 'Dropshippers', 'Dropshippers', 'manage_woocommerce', 'DFM_WooCommerce_Dropshippers', '', 'dashicons-carrot', '55.5000000042' );
            }
            $this->options = $options;
            $this->init();
        }
        /**
         * Initializes the framework by loading
         * required files and functions.
         *
         * @since 1.0
         */
        public function init() {
            $this->constants();
            $this->lib();
            $this->includes();
            $this->actions();
        }
        /**
         * Define constants.
         *
         * @since 1.0
         */
        public function constants() {
            define( 'DFM_SLUG', 'DFM_blog' );
            define( 'DFM_DIR__BASE', dirname( dirname( __FILE__ ) ) );
            define( 'DFM_DIR__INC', DFM_DIR__BASE . '/inc' );
            define( 'DFM_DIR__LIB', DFM_DIR__INC . '/lib' );
            define( 'DFM_DIR__PAGE', DFM_DIR__INC . '/page' );
            define( 'DFM_URL__BASE', plugins_url( '', dirname( __FILE__ ) ) );
            define( 'DFM_URL__ASSETS', DFM_URL__BASE . '/assets' );
            define( 'DFM_URL__IMG', DFM_URL__BASE . '/assets/images' );
            define( 'DFM_URL__JS', DFM_URL__BASE . '/assets/js' );
            define( 'DFM_URL__CSS', DFM_URL__BASE . '/assets/css' );
            define( 'DFM_URL__VENDOR', DFM_URL__BASE . '/assets/vendor' );
            define( 'FPDF_FONTPATH', DFM_DIR__BASE . '/assets/font' );
        }
        /**
         * Define constants.
         *
         * @since 1.0
         */
        public function lib() {
            require_once DFM_DIR__LIB. '/tfpdf.php';
        }
        /**
         * Define constants.
         *
         * @since 1.0
         */
        public function includes() {
            require DFM_DIR__INC. '/functions.php';
            require DFM_DIR__INC. '/functions-widget.php';
            require DFM_DIR__INC. '/functions-metabox.php';
            require DFM_DIR__INC. '/functions-orders.php';
            require DFM_DIR__INC. '/functions-ajax.php';
            require DFM_DIR__INC. '/functions-settings.php';
            require DFM_DIR__INC. '/functions-other.php';

            require DFM_DIR__INC. '/class-dfm-dropshippers-settings.php';
                /* ADD DROPSHIPPER'S LIST IN ADMIN MENU */
            add_action('admin_menu', 'dfm_register_dropshippers_list_page');
            function dfm_register_dropshippers_list_page() {
                require_once(sprintf("%s/import-export.php", DFM_DIR__PAGE));
                add_submenu_page(
                    'DFM_WooCommerce_Dropshippers',
                    __('Dropshippers list','woocommerce-dropshippers'),
                    __('Dropshippers list','woocommerce-dropshippers'),
                    'manage_woocommerce',
                    'dfm_drophippers_list_page',
                    'dfm_dropshippers_list_page_callback'
                );
            }
            /** SEND EMAIL TO DROPSHIPPERS **/
            require DFM_DIR__INC."/dropshipper-new-order-email.php";
        }
        /**
         * Register styles and scripts.
         *
         * @since 1.0
         */
        public function actions() {
            add_filter( 'woocommerce_formatted_address_force_country_display', '__return_true', 1);
            add_action( 'wp_enqueue_scripts', array( $this, 'action__scripts'), 9999 );
            add_action( 'admin_enqueue_scripts', array( $this, 'action__scripts'), 9999 );
            add_action( 'admin_menu', 'dfm_dropshipper_order_list', 42 );
            // Hoook into the 'wp_dashboard_setup' action to register our other functions
            add_action('wp_dashboard_setup', 'dfm_dropshipper_add_dashboard_widgets' );
            // Metaboxes
            add_action( 'add_meta_boxes', 'dfm_add_dropshipper_metaboxes' );
            add_action( 'save_post', 'dfm_save_dropshipper', 10, 2 );
            /* ADD DROPSHIPPER COLUMN IN ADMIN ORDERS TABLE */
            //add_filter( 'manage_edit-shop_order_columns', 'dfm_add_dropshippers_column', 500 );
        }
        public function action__scripts() {
            /* styles declaration: */
                $styles = array(
                    DFM_URL__VENDOR . '/remodal/remodal.css',
                    DFM_URL__VENDOR . '/remodal/remodal-default-theme.css',
                    DFM_URL__CSS . '/main.css',
                );
                $i = 0; foreach ($styles as $style) {
                    wp_enqueue_style( DFM_SLUG . '__style-'.$i++, $style );
                }
            /* scripts declaration: */
                $scripts = array(
                    DFM_URL__JS . '/pay_dropshipper.js',
                    DFM_URL__VENDOR . '/remodal/remodal.min.js',
                    // DFM_URL__JS . '/main.js',
                );
                $i = 0; foreach ($scripts as $script) {
                    wp_enqueue_script( DFM_SLUG. '__script-'.$i++, $script, array(), '1.0.0', false );
                }
        }
    }
endif;