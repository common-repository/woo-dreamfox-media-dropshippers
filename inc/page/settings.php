<?php
function dfm_woocommerce_dropshippers_is_logo_image_valid($image_path){
    if(empty($image_path)){
        return true;
    }
    else{
        $return = true;
        $pdf = new tFPDF();
        $pos = strrpos($image_path,'.');
        if(!$pos){
            return false;
        }
        $type = substr($image_path,$pos+1);
        $type = strtolower($type);
        if($type=='png'){
            $f = fopen($image_path,'rb');
            if(!$f){
                return false;
            }
            $return = $pdf->dfm_parsepngstream($f);
            fclose($f);
        }
        return $return;
    }
}

function dfm_woocommerce_dropshippers_validate_settings($input){
    $type = 'updated';
    $message = __('Settings updated', 'woocommerce-dropshippers');;
    if(isset($input['company_logo'])){
        if(! dfm_woocommerce_dropshippers_is_logo_image_valid($input['company_logo'])){
            $input['company_logo'] = '';
            $type = 'error';
            $message = __('Logo image should not be interlaced', 'woocommerce-dropshippers');
        }
    }
    add_settings_error(
        'woocommerce_dropshippers_options',
        esc_attr( 'settings_updated' ),
        $message ,
        $type
    );
    return $input;
}
add_action( 'wp_ajax_woocommerce_dropshippers_get_attachment_path', 'dfm_woocommerce_dropshippers_get_attachment_path_callback' );

function dfm_woocommerce_dropshippers_get_attachment_path_callback() {
    $attachment_id = intval( $_POST['att_id'] );
    $fullsize_path = get_attached_file( $attachment_id );
    echo $fullsize_path;
    wp_die();
}