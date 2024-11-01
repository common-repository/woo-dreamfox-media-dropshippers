<?php
    /* OTHER */
        /** DROPSHIPPER PRICE HOOK IN ADMIN PRODUCTS **/
            // Check if Multidrop extension is active
            add_action( 'save_post', 'dfm_dropshipper_save_admin_simple_dropshipper_price' );

            function dfm_dropshipper_save_admin_simple_dropshipper_price( $post_id ) {
                if (isset($_POST['_inline_edit']) && wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce'))return;
                if(isset($_POST['dropshipper_price'])){
                    $new_data = $_POST['dropshipper_price'];
                    $post_ID = $_POST['post_ID'];
                    update_post_meta($post_ID, '_dfm_dropshipper_price', $new_data) ;
                }
            }
            // Check if Multidrop extension is active
            add_action( 'woocommerce_product_options_pricing', 'dfm_dropshipper_add_admin_dropshipper_price', 10, 2 );

            function dfm_dropshipper_add_admin_dropshipper_price( $loop ){ 
            $drop_price = get_post_meta( get_the_ID(), '_dfm_dropshipper_price', true );
            if(!$drop_price){ $drop_price = ''; }
            ?>
            <tr>
              <td><div>
                  <p class="form-field _regular_price_field ">
                    <label><?php echo __( 'Dropshipper Price','woocommerce-dropshippers' ) . ' ('.get_woocommerce_currency_symbol().')'; ?></label>
                    <input step="any" type="text" class="wc_input_price short" name="dropshipper_price" value="<?php echo $drop_price; ?>"/>
                  </p>
                </div></td>
            </tr>
            <?php }
            // Check if Multidrop extension is active
            //Display Fields
            add_action( 'woocommerce_product_after_variable_attributes', 'dfm_dropshipper_add_admin_variable_dropshipper_price', 10, 3 );
            //JS to add fields for new variations
            add_action( 'woocommerce_product_after_variable_attributes_js', 'dfm_dropshipper_add_admin_variable_dropshipper_price_js' );
            //Save variation fields
            add_action( 'woocommerce_process_product_meta_variable', 'dfm_dropshipper_admin_variable_dropshipper_price_process', 10, 1 );

            function dfm_dropshipper_add_admin_variable_dropshipper_price( $loop, $variation_data, $post ) {
                $dropshipper_price = get_post_meta( $post->ID, '_dfm_dropshipper_price', true);
            ?>
            <tr>
              <td><div>
                <p class="form-row form-row-full">
                  <label><?php echo __( 'Dropshipper Price','woocommerce-dropshippers' ) . ' ('.get_woocommerce_currency_symbol().')'; ?></label>
                  <input  step="any" type="text" size="5" class="wc_input_price short" name="dropshipper[<?php  echo $loop; ?>]" value="<?php 
                    if(!empty($dropshipper_price)){
                        echo $dropshipper_price;
                    }
                  ?>"/>
                </p>
                </div></td>
            </tr>
            <?php
            }

            function dfm_dropshipper_add_admin_variable_dropshipper_price_js() {
            ?>
            <tr>
              <td><div>
                  <label><?php echo __( 'Dropshipper Price', 'woocommerce' ) . ' ('.get_woocommerce_currency_symbol().')'; ?></label>
                  <input step="any" type="text" size="5" name="dropshipper[' + loop + ']" />
                </div></td>
            </tr>
            <?php
            }

            function dfm_dropshipper_admin_variable_dropshipper_price_process( $post_id ) {
                if (isset( $_POST['variable_sku'] ) ) :
                    $variable_sku = $_POST['variable_sku'];
                    $variable_post_id = $_POST['variable_post_id'];
                    $dropshipper_field = $_POST['dropshipper'];
                    for ( $i = 0; $i < sizeof( $variable_sku ); $i++ ) :
                        $variation_id = (int) $variable_post_id[$i];
                        if ( isset( $dropshipper_field[$i] ) ) {
                            update_post_meta( $variation_id, '_dfm_dropshipper_price', stripslashes( $dropshipper_field[$i] ) );
                            update_post_meta( $variation_id, '_parent_product', $post_id );
                        }
                    endfor;
                    update_post_meta( $post_id, '_variation_prices', $dropshipper_field );
                    update_post_meta( $post_id, '_dfm_dropshipper_price', '' );
                endif;
            }
            // Check if Multidrop extension is active
            add_action( 'woocommerce_save_product_variation', 'dfm_woocommerce_save_dropshippers_product_variation', 10, 2 );

            function dfm_woocommerce_save_dropshippers_product_variation( $variation, $index ){
                if (isset( $_POST['variable_sku'] ) ) :
                    $post_ids = $_POST['variable_post_id'];
                    $dropshipper_field = $_POST['dropshipper'];
                    foreach ($post_ids as $key => $variation_id) {
                        update_post_meta( $variation_id, '_dfm_dropshipper_price', stripslashes( $dropshipper_field[$key] ) );
                    }
                endif;
            }
            /* ADD RESET EARNINGS AJAX IN DROPSHIPPERS LIST */
            add_action('wp_ajax_reset_earnings', 'dfm_reset_earnings_callback');

            function dfm_reset_earnings_callback() {
                check_ajax_referer( 'SpaceRubberDuck', 'security' );
                if(isset($_POST['id'])){
                    $id = intval( $_POST['id'] );
                    update_user_meta($id, 'dfm_dropshipper_earnings', 0);
                    echo 'true';
                }
                else{
                    echo 'false';
                }
                die(); // this is required to return a proper result
            }
            /* AJAX SLIP REQUEST FOR DROPSHIPPERS */
            require_once(sprintf("%s/dropshipper-slip.php", DFM_DIR__PAGE));
            /* ADD MULTILINGUAL SUPPORT */
            add_action( 'plugins_loaded', 'dfm_woocommerce_dropshippers_load_textdomain' );

            function dfm_woocommerce_dropshippers_load_textdomain() {
              load_plugin_textdomain( 'woocommerce-dropshippers', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' ); 
            }
            /* DROPSHIPPERS EDITING SHIPPING INFO */
            add_action( 'admin_footer', 'dfm_dropshipper_edit_shipping_info' );

            function dfm_dropshipper_edit_shipping_info() {
                if ( current_user_can( 'show_dropshipper_widget' ) && (!in_array('administrator', wp_get_current_user()->roles)) )  {
            ?>
                <script type="text/javascript" >

                function js_save_dropshipper_shipping_info(my_order_id, my_info) {
                    var data = {
                        action: 'dropshipper_shipping_info_edited',
                        id: my_order_id,
                        info: my_info
                    };
                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php

                    jQuery.post(ajaxurl, data, function(response) {
                        if(response == 'true'){
                            jQuery('#dropshipper_shipping_info_'+my_order_id+' .dropshipper_date').html(jQuery('#input-dialog-date').val());
                            jQuery('#dropshipper_shipping_info_'+my_order_id+' .dropshipper_tracking_number').html(jQuery('#input-dialog-trackingnumber').val());
                            jQuery('#dropshipper_shipping_info_'+my_order_id+' .dropshipper_shipping_company').html(jQuery('#input-dialog-shippingcompany').val());
                            jQuery('#dropshipper_shipping_info_'+my_order_id+' .dropshipper_notes').html(jQuery('#input-dialog-notes').val());
                        }
                    });
                }
                </script>
                <?php
                }
            }
            add_action('wp_ajax_dropshipper_shipping_info_edited', 'dfm_dropshipper_shipping_info_edited_callback');

            function dfm_dropshipper_shipping_info_edited_callback() {
                global $wpdb;
                if(isset($_POST['id']) && isset($_POST['info']) ){
                    $id = intval( $_POST['id'] );
                    $info = $_POST['info'];
                    update_post_meta($_POST['id'], 'dfm_dropshipper_shipping_info_'.get_current_user_id(), $info);
                    echo 'true';
                }
                else{
                    echo 'false';
                }
                die(); // this is required to return a proper result
            }
            /* ADD A FEE */
            add_action( 'woocommerce_cart_calculate_fees','dfm_woocommerce_custom_surcharge' );

            function dfm_woocommerce_custom_surcharge() {
                global $woocommerce;
                if ( is_admin() && ! defined( 'DOING_AJAX' ) )
                    return;
                $options = get_option('dfm_woocommerce_dropshippers_options');
                if(isset($options['can_add_droshipping_fee']) && $options['can_add_droshipping_fee']=='Yes'){
                    $customer_country = $woocommerce->customer->get_shipping_country();
                    $cart = $woocommerce->cart->get_cart();
                    $products_ids = array();
                    $products_dropshippers = array();
                    $surcharge = 0;
                    foreach ($cart as $key => $product) {
                        if(! in_array($product['product_id'], $products_ids)){
                            $products_ids[] = $product['product_id'];
                            $dropshipper = get_post_meta( $product['product_id'], 'dfm_woo_dropshipper', true);
                            if(!empty($dropshipper)){
                                $drop_user = get_user_by( 'login', $dropshipper );
                                if(!empty($drop_user)){
                                    $drop_user_id = $drop_user->ID;
                                }
                                else{
                                    $drop_user_id = 0;
                                }
                                $nat = 0;
                                $inter = 0;
                                if(! isset($products_dropshippers[$dropshipper]) ){
                                    //get the values for shipping
                                    $nat = get_user_meta($drop_user_id, 'dfm_national_shipping_price', true);
                                    $inter = get_user_meta($drop_user_id, 'dfm_international_shipping_price', true);
                                    if(empty($nat)){$nat = 0;}
                                    if(empty($inter)){$inter = 0;}
                                    $products_dropshippers[$dropshipper] = array(
                                        'national' => $nat,
                                        'international' => $inter
                                    );
                                    $country = get_user_meta($drop_user_id, 'dfm_dropshipper_country', true);
                                    if(empty($country)){ $country = 'US';}
                                    if($customer_country == $country){
                                        $surcharge += $products_dropshippers[$dropshipper]['national'];
                                    }
                                    else{
                                        $surcharge += $products_dropshippers[$dropshipper]['international'];
                                    }
                                }
                            }
                        }
                    }
                    if($surcharge > 0){
                        // get fee name from admin config
                        $woocommerce->cart->add_fee( 'Dropshipping Fee', $surcharge, false, '' ); // false = tax included
                    }
                }
            }
            /* DROPSHIPPERS BULK ASSIGN */
            add_action( 'admin_footer', 'dfm_dropshippers_bulk_assign' );

            function dfm_dropshippers_bulk_assign() {
                global $pagenow;
                if ( current_user_can( 'manage_woocommerce' ) && $pagenow=='admin.php' && $_GET['page']=='dfm_dropshippers_bulk_assign')  {
            ?>
                <script type="text/javascript" >

                function js_dropshippers_bulk_assign(user, taxonomy, term) {
                    jQuery('.dropassign').hide();
                    jQuery('.dropassign').attr("disabled", "disabled");
                    jQuery('#dropspinner_' + taxonomy).show();
                    jQuery('#bulk-updated').hide(300);
                    jQuery('#bulk-error').hide(300);
                    var data = {
                        action: 'dropshippers_bulk_assign',
                        my_user: user,
                        my_taxonomy: taxonomy,
                        my_term: term,
                    };
                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php

                    jQuery.post(ajaxurl, data, function(response) {
                        if(response == 'true'){
                            jQuery('#bulk-updated').show(300);
                        }
                        else{
                            jQuery('#bulk-error').show(300);
                        }
                        jQuery('.dropassign').show();
                        jQuery('.dropassign').removeAttr("disabled");
                        jQuery('#dropspinner_' + taxonomy).hide();
                    });
                }
                </script>
                <?php
                }
            }
            add_action('wp_ajax_dropshippers_bulk_assign', 'dfm_dropshippers_bulk_assign_callback');

            function dfm_dropshippers_bulk_assign_callback() {
                global $wpdb;
                $args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'product',
                    'tax_query' => array(
                        array(
                            'taxonomy' => $_POST['my_taxonomy'],
                            'field'    => 'slug',
                            'terms'    => $_POST['my_term'],
                        ),
                    ),
                );
                $the_query = new WP_Query( $args );
                // The Loop
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    $id = get_the_ID();
                    update_post_meta($id, 'dfm_woo_dropshipper', $_POST['my_user']);
                }
                wp_reset_postdata();
                echo 'true';
                die(); // this is required to return a proper result
            }
            /* DROPSHIPPERS BULK PRICE */
            add_action( 'admin_footer', 'dfm_dropshippers_bulk_price' );

            function dfm_dropshippers_bulk_price() {
                global $pagenow;
                if ( current_user_can( 'manage_woocommerce' ) && $pagenow=='admin.php' && $_GET['page']=='dfm_dropshippers_bulk_price')  {
            ?>
                <script type="text/javascript" >

                function js_dropshippers_bulk_price(user, operation, value, mode) {
                    jQuery('.dropassign').hide();
                    jQuery('.dropassign').attr("disabled", "disabled");
                    jQuery('.dropspinner').show();
                    jQuery('#bulk-updated').hide(300);
                    jQuery('#bulk-error').hide(300);
                    var data = {
                        action: 'dropshippers_bulk_price',
                        my_user: user,
                        my_operation: operation,
                        my_value: value,
                        my_mode: mode
                    };
                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php

                    jQuery.post(ajaxurl, data, function(response) {
                        if(response == 'true'){
                            jQuery('#bulk-updated').show(300);
                        }
                        else{
                            jQuery('#bulk-error').show(300);
                        }
                        jQuery('.dropassign').show();
                        jQuery('.dropassign').removeAttr("disabled");
                        jQuery('.dropspinner').hide();
                    });
                }
                </script>
                <?php
                }
            }
            add_action('wp_ajax_dropshippers_bulk_price', 'dfm_dropshippers_bulk_price_callback');

            function dfm_dropshippers_bulk_price_callback() {
                global $wpdb;
                $user = $_POST['my_user'];
                $operation = $_POST['my_operation'];
                $value = floatval($_POST['my_value']);
                $mode = $_POST['my_mode'];
                $decimal_sep = wp_specialchars_decode(stripslashes(get_option('dfm_woocommerce_price_decimal_sep')), ENT_QUOTES);
                if($value < 0){
                    $value = 0;
                }
                $the_query = new WP_Query(
                    array(
                        'post_type' => 'product',
                        'meta_key' => 'dfm_woo_dropshipper',
                        'meta_query' => array(
                            'relation' => 'OR',
                            array(
                                'key' => 'dfm_woo_dropshipper',
                                'value' => $_POST['my_user'],
                                'compare' => '=',
                            ),
                            array(
                                'key' => 'dfm_woo_dropshipper',
                                'value' => ':"'. $_POST['my_user'] .'";',
                                'compare' => 'LIKE',
                            )
                        ),
                        'posts_per_page' => -1
                    )
                );
                if($mode == 'drop-price-from-regular'){
                    // The Loop
                    while ( $the_query->have_posts() ) {
                        $the_query->the_post();
                        $id = get_the_ID();
                        $product = new WC_Product_Variable($id);
                        $price = floatval($product->price);
                        $variations = $product->get_available_variations();
                        $new_price = 0;
                        if($operation == '%'){
                            $new_price = $price/100*$value;
                        }
                        elseif($operation == '+'){
                            $new_price = $price - $value;
                            if($new_price < 0) {
                                $new_price = 0;
                            }
                        }
                        $new_price = str_replace('.', $decimal_sep, ''.$new_price);
                        $woo_dropshipper = get_post_meta( $id, 'dfm_woo_dropshipper', true );
                        if(empty($woo_dropshipper) || ($woo_dropshipper == '--')){
                            // do nothing
                        }
                        else{
                            if(is_string($woo_dropshipper)){
                                update_post_meta($id, '_dfm_dropshipper_price', $new_price);
                            }
                            else{
                                $drop_prices = get_post_meta($id, '_dfm_dropshipper_prices', true );
                                if(empty($drop_prices)){
                                    $drop_prices = array(); 
                                }
                                $drop_prices[$user] = $new_price;
                                update_post_meta($id, '_dfm_dropshipper_prices', $drop_prices);
                            }
                        }
                        foreach ($variations as $key => $variation) {
                            $tmp_id = $variation['variation_id'];
                            $tmp_product = new WC_Product_Variation($tmp_id);
                            $tmp_price = floatval($tmp_product->price);
                            $tmp_new_price = 0;
                            if($operation == '%'){
                                $tmp_new_price = $tmp_price/100*$value;
                            }
                            elseif($operation == '+'){
                                $tmp_new_price = $tmp_price - $value;
                                if($tmp_new_price < 0) {
                                    $tmp_new_price = 0;
                                }
                            }
                            $tmp_new_price = str_replace('.', $decimal_sep, ''.$tmp_new_price);
                            $drop_prices = get_post_meta($tmp_id, '_dfm_dropshipper_prices', true );
                            if(empty($woo_dropshipper) || ($woo_dropshipper == '--')){
                                // do nothing
                            }
                            else{
                                if(is_string($woo_dropshipper)){
                                    update_post_meta($tmp_id, '_dfm_dropshipper_price', $tmp_new_price);
                                }
                                else{
                                    $drop_prices = get_post_meta($tmp_id, '_dfm_dropshipper_prices', true );
                                    if(empty($drop_prices)){
                                        $drop_prices = array(); 
                                    }
                                    $drop_prices[$user] = $tmp_new_price;
                                    update_post_meta($tmp_id, '_dfm_dropshipper_prices', $drop_prices);
                                }
                            }
                        }
                    }
                }
                elseif($mode == 'regular-price-from-drop'){
                    // Check if Multidrop extension is active
                    if ( is_plugin_active_for_network('woocommerce-dropshippers-multidrop/woocommerce-dropshippers-multidrop.php') || in_array( 'woocommerce-dropshippers-multidrop/woocommerce-dropshippers-multidrop.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                        $is_multidrop_active = true;
                    }
                    else{
                        $is_multidrop_active = false;
                    }
                    // The Loop
                    while ( $the_query->have_posts() ) {
                        $the_query->the_post();
                        $id = get_the_ID();
                        $product = new WC_Product_Variable($id);
                        $price = get_post_meta( $id, '_dfm_dropshipper_price', true);
                        if(empty($price)){
                            $price = 0;
                        }
                        $price = (float) str_replace($decimal_sep, '.', ''.$price);
                        if($is_multidrop_active){
                            $drop_prices = get_post_meta($id, '_dfm_dropshipper_prices', true );
                            if(empty($drop_prices)){
                                $drop_prices = array(); 
                            }
                            if(isset($drop_prices[$user])){
                                $price = (float) str_replace($decimal_sep, '.', ''.$drop_prices[$user]);
                            }
                        }
                        $variations = $product->get_available_variations();
                        $new_price = 0;
                        if($operation == '%'){
                            $new_price = $price/100*$value;
                        }
                        elseif($operation == '+'){
                            $new_price = $price + $value;
                            if($new_price < 0) {
                                $new_price = 0;
                            }
                        }
                        update_post_meta($id, '_regular_price', $new_price);
                        update_post_meta($id, '_price', $new_price);
                        foreach ($variations as $key => $variation) {
                            $tmp_id = $variation['variation_id'];
                            $tmp_product = new WC_Product_Variation($tmp_id);
                            $tmp_price = floatval($tmp_product->price);
                            $tmp_price = get_post_meta($tmp_id, '_dfm_dropshipper_price', true);
                            if(empty($tmp_price)){
                                $tmp_price = 0;
                            }
                            $tmp_price = (float) str_replace($decimal_sep, '.', ''.$tmp_price);
                            if($is_multidrop_active){
                                $drop_prices = get_post_meta($tmp_id, '_dfm_dropshipper_prices', true );
                                if(empty($drop_prices)){
                                    $drop_prices = array(); 
                                }
                                if(isset($drop_prices[$user])){
                                    $tmp_price = (float) str_replace($decimal_sep, '.', ''.$drop_prices[$user]);
                                }
                            }
                            $tmp_new_price = 0;
                            if($operation == '%'){
                                $tmp_new_price = $tmp_price/100*$value;
                            }
                            elseif($operation == '+'){
                                $tmp_new_price = $tmp_price - $value;
                                if($tmp_new_price < 0) {
                                    $tmp_new_price = 0;
                                }
                            }
                            update_post_meta($tmp_id, '_regular_price', $tmp_new_price);
                            update_post_meta($tmp_id, '_price', $tmp_new_price);
                        }
                    }
                }
                wp_reset_postdata();
                echo 'true';
                die(); // this is required to return a proper result
            }
            /* ADD IMPORT/EXPORT TO ADMIN MENU */
            require_once(sprintf("%s/import-export.php", DFM_DIR__PAGE));

            function dfm_dropshippers_importexport_menu(){
                add_submenu_page(
                    'DFM_WooCommerce_Dropshippers',
                    __('WooCommerce Dropshipper Stock Update','woocommerce-dropshippers'),
                    __('Stock Update','woocommerce-dropshippers'),
                    'manage_woocommerce',
                    'dfm_dropshippers_importexport',
                    'dfm_dropshippers_importexport_page'
                );
            }
            // add_action('admin_menu', 'dfm_dropshippers_importexport_menu');
            // ADD DROPSHIPPER COLUMN AND FILTER

            function dfm_wcd_columns_head($defaults) {
                $new_defaults = array();
                foreach ($defaults as $key => $value) {
                    if($key == 'date'){
                        $new_defaults['dropshipper'] = 'Dropshipper<style>.manage-column.column-dropshipper{width:10%;}</style>';
                    }
                    $new_defaults[$key] = $value;
                }
                return $new_defaults;
            }

            function dfm_wcd_columns_content($column_name, $post_id) {
                if ($column_name == 'dropshipper') {
                    $dropshipper = get_post_meta( $post_id, 'dfm_woo_dropshipper', true );
                    if(empty($dropshipper) || ($dropshipper == '--')){
                        echo '–';
                    }
                    elseif(is_string($dropshipper)){
                        echo $dropshipper;
                    }
                    elseif(is_array($dropshipper)){
                        foreach ($dropshipper as $drop) {
                            echo $drop . "<br>\n";
                        }
                    }
                }
            }
            add_filter('manage_product_posts_columns', 'dfm_wcd_columns_head', 20);
            add_action('manage_product_posts_custom_column', 'dfm_wcd_columns_content', 10, 2);

            function dfm_wcd_add_dropshipper_filter() {
                global $typenow;
                global $wp_query;
                if ($typenow=='product') {
                    $dropshippers = get_users('role=dfm_dropshipper');
                    if(empty($dropshippers)){
                        $dropshippers = array();
                    }
                    echo '<select name="dropshipper-filter"><option value="">Filter by dropshipper</option>';
                    foreach ($dropshippers as $drop_usr) {
                        echo '<option value="'.$drop_usr->user_login.'">'.$drop_usr->user_login. '</option>';
                    }
                    echo '</select>';
                }
            }
            add_action('restrict_manage_posts','dfm_wcd_add_dropshipper_filter');

            function dfm_wcd_use_dropshipper_filter($query) {
                global $pagenow;
                $qv = &$query->query_vars;
                if ($pagenow=='edit.php' && isset($_GET['dropshipper-filter'])) {
                    if(!empty($_GET['dropshipper-filter'])){
                        $qv['meta_query'][]= array(
                            'relation' => 'OR',
                            array(
                                'key' => 'dfm_woo_dropshipper',
                                'value' => $_GET['dropshipper-filter'],
                                'compare' => '=',
                            ),
                            array(
                                'key' => 'dfm_woo_dropshipper',
                                'value' => ':"'. $_GET['dropshipper-filter'] .'";',
                                'compare' => 'LIKE',
                            )
                        );
                    }
                }
            }
            add_filter('parse_query','dfm_wcd_use_dropshipper_filter');
        add_action('wp_ajax_woocommerce_dropshippers_mark_as_shipped', 'dfm_woocommerce_dropshippers_mark_as_shipped_callback');
        add_action('wp_ajax_nopriv_woocommerce_dropshippers_mark_as_shipped', 'dfm_woocommerce_dropshippers_mark_as_shipped_callback');

        function dfm_woocommerce_dropshippers_mark_as_shipped_callback(){
            $one_time_keys = get_option('dfm_woocommerce_dropshippers_one_time_keys');
            if(empty($one_time_keys)){
                $one_time_keys = array();
            }
            if(empty($_GET['otk'])){
                echo '<h1>'. __('Order Shipping Notification Error','woocommerce-dropshippers') .'</h1>';
                echo '<p>'. __('There was a problem in marking the order you requested as shipped.<br>This is likely due to an error, or the order has already been marked as shipped.<br>Please login to your dropshipper dashboard to check the status.<br>Thanks!','woocommerce-dropshippers') .'</p>';
            }
            else{
                if(!isset($one_time_keys[$_GET['otk']])){
                    echo '<h1>'. __('Order Shipping Notification Error','woocommerce-dropshippers') .'</h1>';
                    echo '<p>'. __('There was a problem in marking the order you requested as shipped.<br>This is likely due to an error, or the order has already been marked as shipped.<br>Please login to your dropshipper dashboard to check the status.<br>Thanks!','woocommerce-dropshippers') .'</p>';
                }
                else{
                    $order_id = $one_time_keys[$_GET['otk']]['order_id'];
                    $user_login = $one_time_keys[$_GET['otk']]['user_login'];
                    $user = get_user_by('login', $user_login);
                    $user_id = $user->ID;
                    $result = dfm_woocommerce_dropshippers_dropshipped($order_id, $user_id, $user_login);
                    if($result == 'true'){
                        $order = new WC_Order($order_id);
                        $order_number = $order->get_order_number();
                        echo '<h1>'. str_replace('#NUM#', $order_number, __('Order ##NUM# Shipping Notification','woocommerce-dropshippers') ) .'</h1>';
                        echo '<p>'. str_replace('#NUM#', $order_number, __('The order <strong>##NUM#</strong> has been notified as shipped to the store owner.<br>Thanks!','woocommerce-dropshippers') ) .'</p>';
                    }
                    else{
                        echo '<h1>'. __('Order Shipping Notification Error','woocommerce-dropshippers') .'</h1>';
                        echo '<p>'. __('There was a problem in marking the order you requested as shipped.<br>This is likely due to an error, or the order has already been marked as shipped.<br>Please login to your dropshipper dashboard to check the status.<br>Thanks!','woocommerce-dropshippers') .'</p>';
                    }
                }
            }
            die();
        }


    function dfm_prepare_active_options($options) {
        if(!$options){
            $options = array(
                'text_string' => 'No',
                'billing_address' => "Admin's Billing Address\nSomewhere, 42\nPlanet Earth\n00000",
                'can_see_email' => 'Yes',
                'can_see_phone' => 'No',
                'company_logo' => '',
                'slip_footer' => '',
                'admin_email' => '',
                'can_see_email_shipping' => 'Yes',
                'can_put_order_to_completed' => 'No',
                'can_add_droshipping_fee' => 'No',
                'can_see_customer_order_notes' => 'No',
                'show_prices' => 'Yes',
                'send_pdf' => 'Yes',
            );
        }
        else{
            if(! isset($options['can_see_email']))
                $options['can_see_email'] = 'Yes';
            if(! isset($options['can_see_phone']))
                $options['can_see_phone'] = 'No';
            if(! isset($options['company_logo']))
                $options['company_logo'] = '';
            if(! isset($options['slip_footer']))
                $options['slip_footer'] = '';
            if(! isset($options['admin_email']))
                $options['admin_email'] = '';
            if(! isset($options['can_see_email_shipping']))
                $options['can_see_email_shipping'] = 'Yes';
            if(! isset($options['can_put_order_to_completed']))
                $options['can_put_order_to_completed'] = 'No';
            if(! isset($options['can_add_droshipping_fee']))
                $options['can_add_droshipping_fee'] = 'No';
            if(! isset($options['can_see_customer_order_notes']))
                $options['can_see_customer_order_notes'] = 'No';
            if(! isset($options['show_prices']))
                $options['show_prices'] = 'No';
            if(! isset($options['send_pdf']))
                $options['send_pdf'] = 'Yes';
        }

        return $options;
    }
?>