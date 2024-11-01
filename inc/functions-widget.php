<?php
/* WIDGET */
        // Add the widget for dropshippers

        function dfm_dropshipper_dashboard_right_now_function() {
            $current_user = wp_get_current_user()->user_login;
            $product_count = 0;
            $total_sales = 0;
            $orders_processing = 0;
            $orders_completed = 0;
            $table_string = '';
            $query = new WP_Query( array(
                    'post_type' => 'product',
                    'meta_key' => 'dfm_woo_dropshipper',
                    'meta_query' => array(
                        array(
                            'key' => 'dfm_woo_dropshipper',
                            'value' => $current_user,
                            'compare' => '=',
                        )
                    ),
                    'posts_per_page' => -1
                )
            );
            // The Loop
            if ( $query->have_posts() ) {
                $product_count = $query->post_count;
                while ( $query->have_posts() ) {
                    $variations_string = '<strong>'. __('No Options', 'woocommerce-dropshippers') .'</strong>';
                    $query->the_post();
                    $price = get_post_meta( get_the_ID(), '_sale_price', true);
                    $product_sales = (int)get_post_meta(get_the_ID(), 'total_sales', true);
                    $total_sales += $product_sales;
                    $prod = wc_get_product(get_the_ID());
                    $url = get_permalink(get_the_ID());
                    //var_dump($prod->get_attributes());
                    $product_type = '';
                    if(method_exists('WC_Product_Factory', 'get_product_type')){
                        $product_type = WC_Product_Factory::get_product_type(get_the_ID());
                    }
                    else{
                        $product_type = $prod->product_type;
                    }
                    if($product_type == 'variable'){
                        $variations_string = '';
                        $attrs = $prod->get_variation_attributes();
                        if( is_array( $attrs ) && count( $attrs ) > 0 ) {
                            foreach ($attrs as $key => $value) {
                                $variations_string .= '<strong>' . $key . '</strong>';
                                foreach ($value as $val) {
                                    $variations_string .= '<br/>&ndash; '. $val;
                                }
                                $variations_string .= "<br/>\n";
                            }
                        }
                    }
                    $table_string .= '<tr class="alternate" style="padding: 4px 7px 2px;">';
                    $table_string .= '<td class="column-columnname" style="padding: 4px 7px 2px;"><strong>' . get_the_title() . '</strong><div class="row-actions"><span><a href="'.$url.'">'. __('Product Page', 'woocommerce-dropshippers') .'</a></span></div></td>';
                    $table_string .= '<td class="column-columnname" style="padding: 4px 7px 2px;">' . $variations_string . '</td>';
                    $table_string .= '<td class="column-columnname" style="padding: 4px 7px 2px;"> x' . $product_sales . '</td>';
                    $table_string .= '</tr>';
                }
            } else {
                // no posts found
            }
            /* Restore original Post Data */
            wp_reset_postdata();
            $woo_ver = dfm_dropshipper_get_woo_version_number();
            if($woo_ver >= 2.2){
                $query = new WP_Query(
                    array(
                        'post_type' => 'shop_order',
                        'post_status' => array( 'wc-processing', 'wc-completed' ),
                        'posts_per_page' => -1
                    )
                );
            }
            else{
                $query = new WP_Query(
                    array(
                        'post_type' => 'shop_order',
                        'post_status' => 'publish',
                        'posts_per_page' => -1
                    )
                );
            }
            // The Loop
            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    /* actual product list of the dropshipper */
                    $real_products = array();
                    $query->the_post();
                    $order = new WC_Order(get_the_ID());
                    foreach ($order->get_items() as $item) {
                        if(get_post_meta( $item["product_id"], 'dfm_woo_dropshipper', true) == $current_user){
                            array_push($real_products, $item);
                            break;
                        }
                    }
                    if( (sizeof($real_products) > 0) && ($order->get_status() == "completed") ){
                        $orders_completed++;
                    }
                    if( (sizeof($real_products) > 0) && ($order->get_status() == "processing") ){
                        $orders_processing++;
                    }
                }
            }
            else {
                // no posts found
            }
            /* Restore original Post Data */
            wp_reset_postdata();
            ?>
            <div class="table table_shop_content">
                <p class="sub woocommerce_sub"><?php _e( 'Shop Content','woocommerce-dropshippers'); ?></p>
                <table>
                <tr class="first">
                    <td class="first b b-products"><a href="#"><?php echo $product_count; ?></a></td>
                    <td class="t products"><a href="#"><?php _e('Products','woocommerce-dropshippers'); ?></a></td>
                </tr>
                <tr class="first">
                    <td class="first b b-products"><a href="<?php echo admin_url("admin.php?page=dfm_dropshipper_order_list_page") ?>"><?php echo $total_sales; ?></a></td>
                    <td class="t products"><a href="<?php echo admin_url("admin.php?page=dfm_dropshipper_order_list_page") ?>"><?php _e('Sold','woocommerce-dropshippers'); ?></a></td>
                </tr>
                </table>
            </div>
            <div class="table table_orders">
                <p class="sub woocommerce_sub"><?php _e( 'Orders','woocommerce-dropshippers'); ?></p>
                <table>
                <tr class="first">
                    <td class="b b-pending"><a href="<?php echo admin_url("admin.php?page=dfm_dropshipper_order_list_page") ?>"><?php echo $orders_processing ?></a></td>
                    <td class="last t pending"><a href="<?php echo admin_url("admin.php?page=dfm_dropshipper_order_list_page") ?>"><?php _e('Processing','woocommerce-dropshippers'); ?></a></td>
                </tr>
                <tr class="first">
                    <td class="b b-completed"><a href="<?php echo admin_url("admin.php?page=dfm_dropshipper_order_list_page") ?>"><?php echo $orders_completed; ?></a></td>
                    <td class="last t completed"><a href="<?php echo admin_url("admin.php?page=dfm_dropshipper_order_list_page") ?>"><?php _e('Completed','woocommerce-dropshippers'); ?></a></td>
                </tr>
                </table>
            </div>
            <div class="table total_orders">
                <p class="sub woocommerce_sub"><?php _e( 'Total Earnings','woocommerce-dropshippers'); ?></p>
                <table>
                <tr class="first">
                    <td class="last t"><a href="#"><?php _e('Total','woocommerce-dropshippers'); ?></a></td>
                    <td class="b"><a href="#"><?php
                        $dropshipper_earning = get_user_meta(get_current_user_id(), 'dfm_dropshipper_earnings', true);
                        if(!$dropshipper_earning) $dropshipper_earning = 0;
                        echo '<span class="dfm-toberewritten">'. wc_price((float) $dropshipper_earning) .'</span><span class="dfm-tobereconverted" style="display:none;">'. (float) $dropshipper_earning .'</span>';
                    ?></a></td>
                </tr>
                </table>
            </div>
            <div class="versions"></div>
            <table class="wp-list-table widefat fixed posts" cellspacing="0">
                <thead>
                    <tr>
                        <th id="co" class="manage-column column-columnname" scope="col"><?php echo __('Product','woocommerce-dropshippers'); ?></th>
                        <th id="columnname" class="manage-column column-columnname" scope="col"><?php echo __('Options','woocommerce-dropshippers'); ?></th>
                        <th width="40" id="columnname" class="manage-column column-columnname" scope="col"><?php echo __('Sold','woocommerce-dropshippers'); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="manage-column column-columnname" scope="col"><?php echo __('Product','woocommerce-dropshippers'); ?></th>
                        <th class="manage-column column-columnname" scope="col"><?php echo __('Options','woocommerce-dropshippers'); ?></th>
                        <th class="manage-column column-columnname" scope="col"><?php echo __('Sold','woocommerce-dropshippers'); ?></th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                        echo $table_string; 
                    ?>
                </tbody>
            </table>
            <p></p>
            <?php
                $currency = get_user_meta(get_current_user_id(), 'dfm_dropshipper_currency', true);
                if(!$currency) $currency = 'USD';
                $cur_symbols = array(
                    "USD" => '&#36;',
                    "AUD" => '&#36;',
                    "BDT" => '&#2547;&nbsp;',
                    "BRL" => '&#82;&#36;',
                    "BGN" => '&#1083;&#1074;.',
                    "CAD" => '&#36;',
                    "CLP" => '&#36;',
                    "CNY" => '&yen;',
                    "COP" => '&#36;',
                    "CZK" => '&#75;&#269;',
                    "DKK" => '&#107;&#114;',
                    "EUR" => '&euro;',
                    "HKD" => '&#36;',
                    "HRK" => 'Kn',
                    "HUF" => '&#70;&#116;',
                    "ISK" => 'Kr.',
                    "IDR" => 'Rp',
                    "INR" => 'Rs.',
                    "ILS" => '&#8362;',
                    "JPY" => '&yen;',
                    "KRW" => '&#8361;',
                    "MYR" => '&#82;&#77;',
                    "MXN" => '&#36;',
                    "NGN" => '&#8358;',
                    "NOK" => '&#107;&#114;',
                    "NZD" => '&#36;',
                    "PHP" => '&#8369;',
                    "PLN" => '&#122;&#322;',
                    "GBP" => '&pound;',
                    "RON" => 'lei',
                    "RUB" => '&#1088;&#1091;&#1073;.',
                    "SGD" => '&#36;',
                    "ZAR" => '&#82;',
                    "SEK" => '&#107;&#114;',
                    "CHF" => '&#67;&#72;&#70;',
                    "TWD" => '&#78;&#84;&#36;',
                    "THB" => '&#3647;',
                    "TRY" => '&#84;&#76;',
                    "VND" => '&#8363;',
                );
            ?>
            <script type="text/javascript">
                jQuery.ajax({
                    url:"https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20%28%22<?php echo get_woocommerce_currency() . $currency; ?>%22%29&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=cbfunc",
                    dataType: 'jsonp',
                    jsonp: 'callback',
                    jsonpCallback: 'cbfunc'
                });

                function cbfunc(data) {
                    var convRate = data.query.results.rate.Rate;
                    var toRewrite = jQuery('.dfm-toberewritten');

                    jQuery('.dfm-tobereconverted').each(function(i,j){
                        toRewrite.eq(i).html('<?php echo $cur_symbols[$currency]; ?> '+ (parseFloat(jQuery(j).text())*convRate).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                    });
                }

                Number.prototype.format = function(n, x) {
                    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
                    return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
                };
            </script>
            <?php
        }

        // Create the function use in the action hook

        function dfm_dropshipper_add_dashboard_widgets() {
            if (current_user_can('show_dropshipper_widget') && ( (!in_array('administrator', wp_get_current_user()->roles)) && (!is_super_admin()) ) ) {
                // Check if Multidrop extension is active

                wp_add_dashboard_widget('dfm_woocommerce_dashboard_right_now', __('WooCommerce Dropshipper Right Now','woocommerce-dropshippers'), 'dfm_dropshipper_dashboard_right_now_function');
            }
        }
?>