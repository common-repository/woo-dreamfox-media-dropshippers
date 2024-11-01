<?php
    function dfm_dropshippers_get_post_meta($post_id, $meta_key, $single) {
        $metas = get_post_meta($post_id, 'dfm_dropshippers', false);
        if(empty($metas)){
            return array();
        }
        else{   
            return end($metas);
        }
    }

    function dfm_dropshippers_is_logo_image_valid($image_path){
        if(empty($image_path)){
            return true;
        }
        else{
            $return = true;
            $pdf = new tFPDF();
            $pos = strrpos($image_path,'.');
            if(!$pos){
                return false; // Image file has no extension and no type was specified
            }
            $type = substr($image_path,$pos+1);
            $type = strtolower($type);
            if($type=='png'){
                $f = fopen($image_path,'rb');
                if(!$f){
                    return false; // 'Can't open image file
                }
                $return = $pdf->dfm_parsepngstream($f);
                fclose($f);
            }
            return $return;
        }
    }

    function dfm_dropshippers_validate_settings($input){
        $type = 'updated';
        $message = __('Settings updated', 'dfm-woocommerce-dropshippers');;
        if(isset($input['company_logo'])){
            if(! dfm_dropshippers_is_logo_image_valid($input['company_logo'])){
                $input['company_logo'] = '';
                $type = 'error';
                $message = __('Logo image should not be interlaced', 'dfm-woocommerce-dropshippers');
            }
        }
        add_settings_error(
            'dfm_dropshippers_options',
            esc_attr( 'settings_updated' ),
            $message , // message
            $type // ('error' or 'updated')
        );
        return $input;
    }

    function dfm_dropshipper_order_list() {

        add_menu_page( __('Dropshipper Orders','woocommerce-dropshippers'), __('Order list','woocommerce-dropshippers'), 'show_dropshipper_widget', 'dfm_dropshipper_order_list_page', 'dfm_dropshipper_order_list_function', 'dashicons-list-view', '200.42' );
    }

    function dfm_dropshipper_order_list_function() {
        if ( !current_user_can( 'show_dropshipper_widget' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        require_once DFM_DIR__PAGE. '/orders.php';
    }

    function dfm_dropshipper_get_woo_version_number() {

        if ( ! function_exists( 'get_plugins' ) )
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $plugin_folder = get_plugins( '/' . 'woocommerce' );
        $plugin_file = 'woocommerce.php';
        if ( isset( $plugin_folder[$plugin_file]['Version'] ) )
            return $plugin_folder[$plugin_file]['Version'];
        else
            return NULL;
    }
?>