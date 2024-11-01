<?php
    /* ADD DROPSHIPPER COLUMN IN ADMIN ORDERS TABLE */

            function dfm_add_dropshippers_column($columns){
                $columns['dropshippers'] = __('Dropshippers','woocommerce-dropshippers');
                return $columns;
            }
            add_filter( 'manage_edit-shop_order_columns', 'dfm_add_dropshippers_column', 500 );

            function dfm_add_dropshippers_values_in_column($column){
                global $post, $the_order;
                $order_number = $post->ID;
                //start editing, I was saving my fields for the orders as custom post meta
                //if you did the same, follow this code
                if ( $column == 'dropshippers' ) {
                    $row_dropshppers = dfm_dropshippers_get_post_meta($post->ID, 'dropshippers', true);
                    if( is_array( $row_dropshppers ) && count( $row_dropshppers ) > 0 ) {
                        foreach ($row_dropshppers as $dropuser => $value) {
                            $mark_type = 'processing';
                            if($value == 'Shipped'){
                                $mark_type = 'completed';
                            }
                            echo '<mark class="order-status status-'.$mark_type.' tips" data-tip="'. $dropuser .': '. $mark_type .'" style="display:inline-block; margin-bottom:5px;"><span>'.$mark_type.'</span></mark>';
                        }
                    }
                }
                //stop editing
            }
            add_action( 'manage_shop_order_posts_custom_column', 'dfm_add_dropshippers_values_in_column', 2 );
            /* ADD METABOX WITH DROPSHIPPER STATUSES IN ADMIN ORDERS */

            function dfm_print_dropshipper_list_metabox_in_orders(){
                global $post;
                $row_dropshppers = dfm_dropshippers_get_post_meta($post->ID, 'dropshippers', true);
                if( is_array( $row_dropshppers ) && count( $row_dropshppers ) > 0 ) {
                    foreach ($row_dropshppers as $dropuser => $value) {
                        $mydropuser = get_user_by('login', $dropuser);
                        if($mydropuser){
                            $dropshipper_shipping_info = get_post_meta($post->ID, 'dfm_dropshipper_shipping_info_'.$mydropuser->ID, true);
                            if(!$dropshipper_shipping_info){
                                $dropshipper_shipping_info = array(
                                    'date' => '-',
                                    'tracking_number' => '-',
                                    'shipping_company' => '-',
                                    'notes' => '-'
                                );
                            }
                            echo '<h2>'. $dropuser .'</h2>'."\n";
                            echo '<strong>'. __('Date', 'woocommerce-dropshippers') .'</strong>: <span class="dropshipper_date">'. (empty($dropshipper_shipping_info['date'])? '-' :$dropshipper_shipping_info['date']) . '</span><br/>' ."\n";
                            echo '<strong>'. __('Tracking Number(s)', 'woocommerce-dropshippers') .'</strong>: <span class="dropshipper_tracking_number">'. (empty($dropshipper_shipping_info['tracking_number'])? '-' : $dropshipper_shipping_info['tracking_number']) . '</span><br/>'."\n";
                            echo '<strong>'. __('Shipping Company', 'woocommerce-dropshippers') .'</strong>: <span class="dropshipper_shipping_company">'. (empty($dropshipper_shipping_info['shipping_company'])? '-' : $dropshipper_shipping_info['shipping_company']) . '</span><br/>'."\n";
                            echo '<strong>'. __('Notes', 'woocommerce-dropshippers') .'</strong>: <span class="dropshipper_notes">'. (empty($dropshipper_shipping_info['notes'])? '-' : $dropshipper_shipping_info['notes']) . '</span><br/>'."\n";
                            echo "<hr>\n";
                        }
                    }
                }
            }
            add_action( 'add_meta_boxes', 'dfm_add_dropshipper_metaboxes_in_orders' );

            function dfm_add_dropshipper_metaboxes_in_orders() {
                add_meta_box('dfm_wpt_dropshipper_list', __('Dropshippers','woocommerce-dropshippers'), 'dfm_print_dropshipper_list_metabox_in_orders', 'shop_order', 'side', 'default');
            }
            /* ADD SHIPPED BUTTON IN DROPSHIPPERS ORDERS */
            add_action( 'admin_footer', 'dfm_dropshipped_javascript' );

            function dfm_dropshipped_javascript() {
                if ( current_user_can( 'show_dropshipper_widget' ) && ( (!in_array('administrator', wp_get_current_user()->roles)) && (!is_super_admin()) ) )  {
            ?>
                <script type="text/javascript" >

                function js_dropshipped(my_id) {
                    if(confirm("<?php echo __('Are you sure?','woocommerce-dropshippers');?>")){
                        var data = {
                            action: 'dropshipped',
                            id: my_id
                        };
                        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php

                        jQuery.post(ajaxurl, data, function(response) {
                            if(response == 'true'){
                                jQuery('#mark_dropshipped_' + my_id).after("<?php echo __('Shipped','woocommerce-dropshippers'); ?>");
                                jQuery('.tr-order-'+my_id).addClass('is-shipped');
                            }
                        });
                        jQuery('#mark_dropshipped_' + my_id).fadeOut();
                    }
                    else{
                        // do nothing
                    }
                }
                </script>
                <?php
                }
            }

            function dfm_woocommerce_dropshippers_dropshipped($order_id, $user_id, $user_login){
                global $wpdb;
                if(isset($order_id)){
                    $id = intval( $order_id );
                    $my_wc_order = new WC_Order($order_id);
                    $my_wc_order_number = $my_wc_order->get_order_number();
                    $dropshippers = dfm_dropshippers_get_post_meta($order_id, 'dropshippers', true);
                    $dropshippers[$user_login] = "Shipped";
                    $admin_email = get_option('admin_email');
                    $options = get_option('dfm_woocommerce_dropshippers_options');
                    // Check if should set order status completed
                    if(isset($options['can_put_order_to_completed']) && $options['can_put_order_to_completed']=='Yes'){
                        $can_set_order_shipped = true;
                        foreach ($dropshippers as $tmp_drop) {
                            if($tmp_drop != 'Shipped'){
                                $can_set_order_shipped = false;
                                break;
                            }
                        }
                        if($can_set_order_shipped){
                            $my_wc_order->update_status('completed');
                        }
                    }
                    $dropshipper_shipping_info = get_post_meta($id, 'dfm_dropshipper_shipping_info_'.$user_id, true);
                    if(!$dropshipper_shipping_info){
                        $dropshipper_shipping_info = array(
                            'date' => '',
                            'tracking_number' => '',
                            'shipping_company' => '',
                            'notes' => ''
                        );
                    }
                    update_post_meta($order_id, 'dfm_dropshippers', $dropshippers);
                    if( isset($options['admin_email']) && (!empty($options['admin_email'])) ){
                        $admin_email = $options['admin_email'];
                    }
                    $domain_name =  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);
                    $mail_headers = array('From: "'. get_option('blogname'). '" <no-reply@'. $domain_name .'>');
                    if(!empty($options['admin_email_cc'])){
                         $mail_headers[]= 'Cc: ' . $options['admin_email_cc'] ;
                    }
                    add_filter( 'wp_mail_content_type', 'dropshippers_set_html_content_type' );
                    require_once(WP_PLUGIN_DIR . '/woocommerce/includes/emails/class-wc-email.php');
                    require_once(WP_PLUGIN_DIR . '/woocommerce/includes/libraries/class-emogrifier.php');
                    $emailer = new WC_Email();
                    $emailer_attachments = $emailer->get_attachments();
                    $headers = $emailer->get_headers();
                    if(!empty($options['admin_email_cc'])){
                        if(is_array($headers)){
                            $headers[]= 'Cc: ' . $options['admin_email_cc'] ;
                        }
                        elseif(is_string($headers)){
                            $headers .= 'Cc: ' . $options['admin_email_cc'] ."\r\n";
                        }
                    }
                    $emailer->send( $admin_email, str_replace("%NUMBER%",$my_wc_order_number,__("Dropshipper order update %NUMBER%", 'woocommerce-dropshippers')),
                        str_replace("%NUMBER%",$my_wc_order_number,str_replace("%NAME%",$user_login,__('The Dropshipper %NAME% has shipped order %NUMBER%', 'woocommerce-dropshippers'))) .
                        "<br>\n". __('Date', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['date'] .
                        "<br>\n". __('Tracking Number(s)', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['tracking_number'] .
                        "<br>\n". __('Shipping Company', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['shipping_company'] .
                        "<br>\n". __('Notes', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['notes'],
                        $headers,
                        $emailer_attachments
                    );
                    remove_filter( 'wp_mail_content_type', 'dropshippers_set_html_content_type' );
                    //send the order email to the customer
                    if( isset($options['send_tracking_info']) && ($options['send_tracking_info'] == 'Yes') && (!empty($dropshipper_shipping_info['tracking_number'])) ){
                        $items = $my_wc_order->get_items();
                        $real_items = array();
                        // $user_login
                        foreach ($items as $item_id => $item) {
                            $item['redunfed_items'] = 0;
                            $woo_dropshipper = get_post_meta( $item["product_id"], 'dfm_woo_dropshipper', true);
                            $is_item_for_this_dropshipper == false;
                            if(empty($woo_dropshipper)){
                                $woo_dropshipper == '';
                            }
                            if(is_string($woo_dropshipper)){
                                if($woo_dropshipper == $user_login){
                                    $refunded_items = $my_wc_order->get_qty_refunded_for_item($item_id);
                                    $item['redunfed_items'] = $refunded_items;
                                    if($item['qty'] - $refunded_items > 0){
                                        $real_items[] = $item;
                                    }
                                }
                            }
                            else{
                                if(in_array($user_login, $woo_dropshipper)){
                                    $refunded_items = $my_wc_order->get_qty_refunded_for_item($item_id);
                                    $item['redunfed_items'] = $refunded_items;
                                    if($item['qty'] - $refunded_items > 0){
                                        $real_items[] = $item;
                                    }
                                }
                            }
                        }
                        add_filter( 'wp_mail_content_type', 'dropshippers_set_html_content_type' );
                        ob_start();
                        ?>
                        <div style="background-color: #f5f5f5; width: 100%; -webkit-text-size-adjust: none ; margin: 0; padding: 70px  0  70px  0;">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0" height="100%">
                        <tbody><tr><td valign="top" align="center">
                        <table width="600" cellspacing="0" cellpadding="0" border="0" style="-webkit-box-shadow: 0  0  0  3px  rgba; box-shadow: 0  0  0  3px  rgba; -webkit-border-radius: 6px ; border-radius: 6px ; background-color: #fdfdfd; border: 1px  solid  #dcdcdc; -webkit-border-radius: 6px ; border-radius: 6px ;" id="template_container"><tbody><tr><td valign="top" align="center">
                        <table width="600" cellspacing="0" cellpadding="0" border="0" bgcolor="#557da1" style="background-color: #557da1; color: #ffffff; -webkit-border-top-left-radius: 6px ; -webkit-border-top-right-radius: 6px ; border-top-left-radius: 6px ; border-top-right-radius: 6px ; border-bottom: 0px; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;" id="template_header"><tbody><tr><td>
                            <h1 style="color: #ffffff; margin: 0; padding: 28px  24px; text-shadow: 0  1px  0  #7797b4; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;"><?php echo __('Order Update','woocommerce-dropshippers'); ?></h1>
                        </td></tr></tbody></table></td></tr><tr><td valign="top" align="center">
                        <table width="600" cellspacing="0" cellpadding="0" border="0" id="template_body">
                        <tbody><tr><td valign="top" style="background-color: #fdfdfd; -webkit-border-radius: 6px ; border-radius: 6px ;">
                        <table width="100%" cellspacing="0" cellpadding="20" border="0"><tbody><tr><td valign="top">
                        <div style="color: #737373; font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"><p><?php echo __('The following products were shipped','woocommerce-dropshippers'); ?></p>
                        <table cellspacing="0" cellpadding="6" border="1" style="width: 100%; border: 1px  solid  #eee;">
                        <thead><tr><th style="text-align: left; border: 1px  solid  #eee;"><?php echo __('Product','woocommerce-dropshippers'); ?></th>
                        <th style="text-align: left; border: 1px  solid  #eee;"><?php echo __('Quantity','woocommerce-dropshippers'); ?></th>
                        </tr></thead><tbody>
                        <?php foreach ($real_items as $item) : ?>
                        <?php
                            if($item['variation_id'] > 0){
                                $product_id = $item['variation_id'];
                                $product_from_id = new WC_Product_Variation($product_id);
                                $SKU = $product_from_id->get_sku();
                                if(empty($SKU)){
                                    $product_from_id = new WC_Product($item['product_id']);
                                    $SKU = $product_from_id->get_sku();
                                }
                            }
                            else{
                                $product_id = $item['product_id'];
                                $product_from_id = new WC_Product($product_id);
                                $SKU = $product_from_id->get_sku();
                            }
                            if(empty($SKU)){
                                $SKU = '';
                            }
                            $my_item_post = get_post($item['product_id']);
                            $item_meta = '';
                        ?>
                        <tr>
                            <td style="text-align: left; vertical-align: middle; border: 1px  solid  #eee; word-wrap: break-word;"><?php
                                echo __($my_item_post->post_title) . ' (SKU: '.$SKU.')';
                                if($item['variation_id'] != 0){
                                    if(method_exists($item, 'get_meta_data')){ // new method for WooCommerce 2.7
                                        foreach ($item->get_meta_data() as $product_meta_key => $product_meta_value) {
                                            if(!empty($product_meta_value->id)){
                                                $display_key  = wc_attribute_label( $product_meta_value->key, $product_from_id );
                                                $item_meta .= '<br/><small>' . $display_key . ': ' . $product_meta_value->value . '</small>' . "\n";
                                            }
                                        }
                                    }
                                    else{ // old method
                                        $_product = apply_filters( 'woocommerce_order_item_product', $my_wc_order->get_product_from_item( $item ), $item );
                                        $item_meta_object = new WC_Order_Item_Meta( $item, $product_from_id );
                                        if ( $item_meta_object->meta ){
                                            $item_meta .= '<br/><small>' . nl2br( $item_meta_object->display( true, true ) ) . '</small>' . "\n";
                                        }
                                    }
                                    echo $item_meta;
                                }
                            ?></td>
                            <td style="text-align: left; vertical-align: middle; border: 1px  solid  #eee;"><span class="amount"><?php echo ($item['qty'] - $item['redunfed_items']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        </tfoot></table>
                        </td>
                        </tr></tbody></table></td>
                        </tr></tbody></table></td>
                        </tr><tr><td valign="top" align="center">
                        <table width="100%" cellspacing="0" cellpadding="10" border="0" style="border-top: 0px; -webkit-border-radius: 6px; text-align: left; font-size:20px;"><tbody><tr><td valign="top">
                        <?php echo
                            __('Date', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['date'] .
                            "<br>\n". __('Tracking Number(s)', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['tracking_number'] .
                            "<br>\n". __('Shipping Company', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['shipping_company'];
                        ?>
                        </td></tr></tbody></table>
                        <table width="600" cellspacing="0" cellpadding="10" border="0" style="border-top: 0px; -webkit-border-radius: 6px;" id="template_footer"><tbody><tr><td valign="top">
                        <table width="100%" cellspacing="0" cellpadding="10" border="0"><tbody><tr><td valign="middle" style="border: 0; color: #99b1c7; font-family: Arial; font-size: 12px; line-height: 125%; text-align: center;" id="credit" colspan="2"><p><?php echo bloginfo('name'); ?></p>
                        </td>
                        </tr></tbody></table></td>
                        </tr></tbody></table></td>
                        </tr></tbody></table></td>
                        </tr></tbody></table></div>
                        <?php
                        $email_body = ob_get_clean();
                        $emailer = new WC_Email();
                        $emailer_attachments = $emailer->get_attachments();
                        $headers = $emailer->get_headers();
                        $emailer->send($my_wc_order->billing_email, '' . $my_wc_order->get_order_number() . ' – ' . __('Order Update','woocommerce-dropshippers'), $email_body, $headers, $emailer_attachments );
                        // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
                        remove_filter( 'wp_mail_content_type', 'dropshippers_set_html_content_type' );
                    }
                    //also delete one time keys for dropshippers
                    $one_time_keys = get_option('dfm_woocommerce_dropshippers_one_time_keys');
                    $new_one_time_keys = array();
                    foreach ($one_time_keys as $key => $value) {
                        if(($value['user_login'] == $user_login) && ($value['order_id'] == $order_id) ){
                            //   ¯\(°_o)/¯
                        }
                        else{
                            $new_one_time_keys[$key] = $value;
                        }
                    }
                    update_option('dfm_woocommerce_dropshippers_one_time_keys', $new_one_time_keys);
                    return 'true';
                }
                else{
                    return 'false';
                }
            }
            add_action('wp_ajax_dropshipped', 'dfm_dropshipped_callback');

            function dfm_dropshipped_callback() {
                $user = wp_get_current_user();
                echo dfm_woocommerce_dropshippers_dropshipped($_POST['id'], get_current_user_id(), $user->user_login, true);
                die(); // this is required to return a proper result
            }
            /* REMOVE ADMIN PANELS */

            function dfm_dropshippers_remove_menus () {
                if ( current_user_can( 'show_dropshipper_widget' ) && ( (!in_array('administrator', wp_get_current_user()->roles)) && (!is_super_admin()) ) )  {
                    global $menu;
                    $allowed = array(__('Dashboard'), __('Profile'), __('Products','woocommerce') );
                    end ($menu);
                    while (prev($menu)){
                        $value = explode(' ',$menu[key($menu)][0]);
                        if(!in_array($value[0] != NULL?$value[0]:"" , $allowed)){unset($menu[key($menu)]);}
                    }
                }
            }
            add_action('admin_menu', 'dfm_dropshippers_remove_menus');

            function dfm_dropshippers_disable_dashboard_widgets() {  
                if ( current_user_can( 'show_dropshipper_widget' ) && ( (!in_array('administrator', wp_get_current_user()->roles)) && (!is_super_admin()) ) )  {
                    remove_action('welcome_panel', 'wp_welcome_panel');
                    remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
                    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');   // Right Now
                    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // Recent Comments
                    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');  // Incoming Links
                    remove_meta_box('dashboard_plugins', 'dashboard', 'normal');   // Plugins
                    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');  // Quick Press
                    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');  // Recent Drafts
                    remove_meta_box('dashboard_primary', 'dashboard', 'side');   // WordPress blog
                    remove_meta_box('dashboard_secondary', 'dashboard', 'side');   // Other WordPress News
                }
            }  
            add_action('wp_dashboard_setup', 'dfm_dropshippers_disable_dashboard_widgets');

            function dfm_dropshippers_remove_admin_bar_links() {
                global $wp_admin_bar;
                if ( current_user_can( 'show_dropshipper_widget' ) && ( (!in_array('administrator', wp_get_current_user()->roles)) && (!is_super_admin()) ) )  {
                    $wp_admin_bar->remove_menu('updates');        // Remove the updates link
                    $wp_admin_bar->remove_menu('comments');      // Remove the comments link
                    $wp_admin_bar->remove_menu('new-content');    // Remove the content link
                }
            }
            add_action( 'wp_before_admin_bar_render', 'dfm_dropshippers_remove_admin_bar_links' );
?>