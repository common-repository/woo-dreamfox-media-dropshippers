<?php
// AJAX
            add_action('wp_ajax_dropshippers_admin_save_drop_settings', 'dfm_dropshippers_admin_save_drop_setting_callback');

            function dfm_dropshippers_admin_save_drop_setting_callback() {
                check_ajax_referer( 'SpaceRubberDuckSave', 'security' );
                $options = get_option('dfm_woocommerce_dropshippers_options');
                $user_id = $_POST['id'];
                if(!empty($user_id)){
                    if(isset($_POST['dropshipper_paypal_email'])){
                        $dropshipper_paypal_email = $_POST['dropshipper_paypal_email'];
                        $dropshipper_paypal_email = sanitize_email($dropshipper_paypal_email);
                        if(is_email($dropshipper_paypal_email, false)){
                            update_user_meta($user_id, 'dfm_dropshipper_paypal_email', $dropshipper_paypal_email);
                        }
                    }
                    if(isset($_POST['dropshipper_currency'])){
                        $dropshipper_currency = $_POST['dropshipper_currency'];
                        if(empty($dropshipper_currency)) $dropshipper_currency = 'USD';
                        update_user_meta($user_id, 'dfm_dropshipper_currency', $dropshipper_currency);
                    }
                    if(isset($options['can_add_droshipping_fee']) && $options['can_add_droshipping_fee']=='Yes'){
                        if(isset($_POST['dropshipper_country'])){
                            $dropshipper_country = $_POST['dropshipper_country'];
                            update_user_meta($user_id, 'dfm_dropshipper_country', $dropshipper_country);
                        }
                        if(isset($_POST['dropshipper_national_shipping_price'])){
                            $dropshipper_national_shipping_price = $_POST['dropshipper_national_shipping_price'];
                            update_user_meta($user_id, 'dfm_national_shipping_price', $dropshipper_national_shipping_price);
                        }
                        if(isset($_POST['dropshipper_international_shipping_price'])){
                            $dropshipper_international_shipping_price = $_POST['dropshipper_international_shipping_price'];
                            update_user_meta($user_id, 'dfm_international_shipping_price', $dropshipper_international_shipping_price);
                        }
                    }
                }
                die(); // this is required to return a proper result
            }

            function dfm_dropshippers_list_page_callback() {
                if( isset($_POST['single-dropshipper-csv-import']) && isset($_POST['security']) && isset($_POST['drop-id']) ){
                    if(wp_verify_nonce( $_POST['security'], 'SpaceRubberDuckCSV')) {
                        $user_id = $_POST['drop-id'];
                        if(isset($_POST['csv-column-delimiter-' . $user_id]) && isset($_POST['csv-sku-column-' . $user_id]) && isset($_POST['csv-quantity-column-' . $user_id]) && isset($_FILES['csv-file-' . $user_id])){
                            $csv_column_delimiter = $_POST['csv-column-delimiter-' . $user_id];
                            $csv_sku_column =  $_POST['csv-sku-column-' . $user_id];
                            $csv_quantity_column = $_POST['csv-quantity-column-' . $user_id];
                            update_user_meta($user_id, 'dfm_dropshipper_csv_column_delimiter', $csv_column_delimiter);
                            update_user_meta($user_id, 'dfm_dropshipper_csv_sku_column_number', $csv_sku_column);
                            update_user_meta($user_id, 'dfm_dropshipper_csv_quantity_column_number', $csv_quantity_column);
                            $done_products = 0;
                            try {
                                $there_was_an_error = false;
                                // Undefined | Multiple Files | $_FILES Corruption Attack
                                // If this request falls under any of them, treat it invalid.
                                if ( !isset($_FILES['csv-file-'.$user_id]['error']) || is_array($_FILES['csv-file-'.$user_id]['error']) ) {
                                    $there_was_an_error = true;
                                    throw new Exception(__('Invalid file format.','woocommerce-dropshippers'));
                                }
                                // Check $_FILES['import-file']['error'] value.
                                if(!$there_was_an_error){
                                    switch ($_FILES['csv-file-'.$user_id]['error']) {
                                        case UPLOAD_ERR_OK:
                                            break;
                                        case UPLOAD_ERR_NO_FILE:
                                            $there_was_an_error = true;
                                            throw new Exception(__('No file sent.','woocommerce-dropshippers'));
                                            break;
                                        case UPLOAD_ERR_INI_SIZE:
                                        case UPLOAD_ERR_FORM_SIZE:
                                            $there_was_an_error = true;
                                            throw new Exception(__('Exceeded filesize limit.','woocommerce-dropshippers'));
                                            break;
                                        default:
                                            $there_was_an_error = true;
                                            throw new Exception(__('Unknown errors.','woocommerce-dropshippers'));
                                            break;
                                    }
                                    // You should also check filesize here.
                                    if ($_FILES['csv-file-'.$user_id]['size'] > 1000000) {
                                        $there_was_an_error = true;
                                        throw new Exception(__('Exceeded filesize limit.','woocommerce-dropshippers'));
                                    }
                                    // DO NOT TRUST $_FILES['import-file']['mime'] VALUE !!
                                    // Check MIME Type by yourself.
                                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                                    if (false === $ext = array_search(
                                        $finfo->file($_FILES['csv-file-'.$user_id]['tmp_name']),
                                        array(
                                            'text/csv',
                                            'text/html',
                                            'text/plain'
                                        ),
                                        true
                                    )) {
                                        $there_was_an_error = true;
                                        throw new Exception(__('Invalid file format.','woocommerce-dropshippers'));
                                    }
                                }
                                $csv = woocommerce_dropshippers_csv_to_array($_FILES['csv-file-'.$user_id]['tmp_name'], $csv_column_delimiter, '"');
                                if($csv && !empty($csv)){
                                    foreach ($csv as $csv_line => $csv_values){
                                        $column_number = 1;
                                        $sku_found = false;
                                        $quantity_found = false;
                                        if(!empty($csv_values)){
                                            foreach ($csv_values as $csv_value_key => $csv_value_value) {
                                                if($column_number == $csv_sku_column){ //sku found
                                                    $sku_found = $csv_value_value;
                                                }
                                                if($column_number == $csv_quantity_column){ //quantity found
                                                    $quantity_found = $csv_value_value;
                                                }
                                                $column_number++;
                                            }
                                            if( ($sku_found !== false) && (!empty($sku_found)) && ($quantity_found !== false) && ($quantity_found !== '') ){
                                                $product_id = wc_get_product_id_by_sku($sku_found);
                                                if(!empty($product_id)){
                                                    $product = wc_get_product($product_id);

                                                    if(function_exists('wc_update_product_stock')){
                                                        wc_update_product_stock($product_id, $quantity_found);
                                                    }
                                                    else{
                                                        $product->set_stock($quantity_found);
                                                    }
                                                    $done_products++;
                                                }
                                            }
                                        }
                                    }
                                }
                                else{
                                    $there_was_an_error = true;
                                    throw new Exception(__('CSV is empty.','woocommerce-dropshippers'));
                                }
                                echo '<div class="notice notice-success is-dismissible"><p>'. str_replace('%NUMBER%', $done_products, __('Products successfully updated: %NUMBER%','woocommerce-dropshippers') ) .'</p></div>';
                                $there_was_an_error = true;
                            } catch (Exception $e) {
                                echo '<div class="notice notice-error is-dismissible"><p>'.$e->getMessage().'</p></div>';
                                $there_was_an_error = true;
                            }
                        }
                    }
                }
                $options = get_option('dfm_woocommerce_dropshippers_options');
                ?>
                <div class="dropshippers-header" style="margin:0; padding:0; width:100%; height:100px; background: url('<?php echo DFM_URL__IMG . '/headerbg.png' ?>'); background-repeat: repeat-x;">
                    <img src="<?php echo DFM_URL__IMG . '/woocommerce-dropshippers-header.png' ?>" style="margin:0; padding:0; width:auto; height:100px;">
                </div>
                <?php
                echo '<script type="text/javascript" src="'. DFM_URL__JS . '/pay_dropshipper.js' .'"></script>';
                echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
                echo '<h2>'. __('WooCommerce Dropshippers','woocommerce-dropshippers') .'</h2>';
                echo '<h3>'. __('Dropshippers List','woocommerce-dropshippers') .'</h3>';
                $ajax_nonce_save = wp_create_nonce( "SpaceRubberDuckSave" );
                $csv_nonce = wp_create_nonce( "SpaceRubberDuckCSV" );
                $ajax_nonce = wp_create_nonce( "SpaceRubberDuck" );
                ?>
                <style type="text/css">
                .dropshipper-overlay {
                    height: 100%;
                    width: 100%;
                    position: fixed;
                    z-index: 100000;
                    left: 0;
                    top: 0;
                    background-color: rgb(0,0,0);
                    background-color: rgba(0,0,0, 0.9);
                    overflow-x: hidden;
                }
                </style>
                <script type="text/javascript">

                    function js_reset_earnings(my_id) {
                        if(confirm("<?php echo __('Do you really want to reset the earnings of this dropshipper?','woocommerce-dropshippers'); ?>")){
                            var data = {
                                action: 'reset_earnings',
                                security: '<?php echo $ajax_nonce; ?>',
                                id: my_id
                            };

                            jQuery.post(ajaxurl, data, function(response) {
                                if(response == 'true'){
                                    location.reload(true);
                                }
                            });
                        }
                        else{
                            // do nothing
                        }
                    }

                    function toggleDropRow(user_id){
                        jQuery('.user-details-row-'+ user_id +' .user-details-content').slideToggle();
                        var currentDirection = jQuery('.user-row-'+ user_id +' .drop-toggle').text();
                        if(currentDirection == '▼'){
                            jQuery('.user-row-'+ user_id +' .drop-toggle').text('▲');
                        }
                        else if(currentDirection == '▲'){
                            jQuery('.user-row-'+ user_id +' .drop-toggle').text('▼');
                        }
                    }

                    function saveDropSettings(user_id){
                        var dropshipper_paypal_email = jQuery('.user-details-row-'+ user_id + ' input[name="dropshipper_paypal_email"]').val();
                        if(!dropshipper_paypal_email) dropshipper_paypal_email = '';
                        var dropshipper_currency = jQuery('.user-details-row-'+ user_id + ' select[name="dropshipper_currency"]').val();
                        if(!dropshipper_currency) dropshipper_currency = 'USD';
                        var dropshipper_country = jQuery('.user-details-row-'+ user_id + ' select[name="dropshipper_country"]').val();
                        if(!dropshipper_country) dropshipper_country = '';
                        var dropshipper_national_shipping_price = jQuery('.user-details-row-'+ user_id + ' input[name="dropshipper_national_shipping_price"]').val();
                        if(!dropshipper_national_shipping_price) dropshipper_national_shipping_price = '';
                        var dropshipper_international_shipping_price = jQuery('.user-details-row-'+ user_id + ' input[name="dropshipper_international_shipping_price"]').val();
                        if(!dropshipper_international_shipping_price) dropshipper_international_shipping_price = '';
                        jQuery('body').append('<div class="dropshipper-overlay" style="display:none"></div>');
                        jQuery('.dropshipper-overlay').fadeIn(200);
                        var data = {
                            action: 'dropshippers_admin_save_drop_settings',
                            security: '<?php echo $ajax_nonce_save; ?>',
                            id: user_id,
                            dropshipper_paypal_email: dropshipper_paypal_email,
                            dropshipper_currency: dropshipper_currency,
                            dropshipper_country: dropshipper_country,
                            dropshipper_national_shipping_price: dropshipper_national_shipping_price,
                            dropshipper_international_shipping_price: dropshipper_international_shipping_price
                        };

                        jQuery.post(ajaxurl, data, function(response) {
                            alert("<?php _e('Settings saved.','woocommerce-dropshippers') ?>");
                            location.reload(true);
                        });
                    }
                </script>
                <table class="wp-list-table widefat fixed posts striped" cellspacing="0">
                <thead>
                    <tr>
                        <th class="manage-column column-columnname" scope="col"><?php echo __('User','woocommerce-dropshippers'); ?></th>
                        <th class="manage-column column-columnname" scope="col"><?php echo __('Earnings','woocommerce-dropshippers'); ?></th>
                        <th class="manage-column column-columnname" scope="col"><?php echo __('Actions','woocommerce-dropshippers'); ?></th>
                        <th width="20"></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="manage-column column-columnname" scope="col"><?php echo __('User','woocommerce-dropshippers'); ?></th>
                        <th class="manage-column column-columnname" scope="col"><?php echo __('Earnings','woocommerce-dropshippers'); ?></th>
                        <th class="manage-column column-columnname" scope="col"><?php echo __('Actions', 'woocommerce-dropshippers'); ?></th>
                        <th width="20"></th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                        $countries_obj = new WC_Countries();
                        $countries = $countries_obj->__get('countries');
                        $dropshipperz = get_users('role=dfm_dropshipper');
                        foreach ($dropshipperz as $drop_usr) {
                            echo '<tr class="type-shop_order user-row-'. $drop_usr->ID .'"><td><strong>'.$drop_usr->user_login.'</strong></td>';
                            $dropshipper_earning = get_user_meta($drop_usr->ID, 'dfm_dropshipper_earnings', true);
                            if(!$dropshipper_earning){ $dropshipper_earning = 0; }
                            echo '<td>'. wc_price((float) $dropshipper_earning).'</td>';
                            echo '<td>';
                            echo '<button class="button button-primary" style="margin-bottom: 3px;" onclick="js_reset_earnings(\''. $drop_usr->ID .'\')">'. __('Reset earnings','woocommerce-dropshippers') .'</button><br/>';
                            $email = get_user_meta($drop_usr->ID, 'dfm_dropshipper_paypal_email',true);
                            if($email){
                                echo '<button class="button button-primary" onclick="payDropshipper(\''. $email .'\', \''.$dropshipper_earning.'\', \''.get_woocommerce_currency().'\')">'. __('Pay this dropshipper (PayPal)','woocommerce-dropshippers') .'</button>';
                            }
                            else{
                                echo __('The dropshipper has not entered the PayPal email','woocommerce-dropshippers');
                            }
                            echo '</td>';
                            echo '<td><a class="drop-toggle" href="#" onclick="toggleDropRow('. $drop_usr->ID .'); return false;" >▼</a></td>';
                            echo '</tr>' . "\n";
                            $dropshipper_paypal_email = get_user_meta($drop_usr->ID, 'dfm_dropshipper_paypal_email', true);
                            $dropshipper_country = get_user_meta($drop_usr->ID, 'dfm_dropshipper_country', true);
                            if(empty($dropshipper_country)) $dropshipper_country = 'US';
                            $dropshipper_national_shipping_price = get_user_meta($drop_usr->ID, 'dfm_national_shipping_price', true);
                            if(empty($dropshipper_national_shipping_price)) $dropshipper_national_shipping_price = 0;
                            $dropshipper_international_shipping_price = get_user_meta($drop_usr->ID, 'dfm_international_shipping_price', true);
                            if(empty($dropshipper_international_shipping_price)) $dropshipper_international_shipping_price = 0;
                            $dropshipper_currency = get_user_meta($drop_usr->ID, 'dfm_dropshipper_currency', true);
                            if(!$dropshipper_currency) $dropshipper_currency = 'USD';
                    ?>
                            <tr class="user-details-row-<?php echo $drop_usr->ID; ?>" >
                                <td colspan="2">
                                    <div class="user-details-content" style="display:none;">
                                        <h3><?php echo __('Dropshipper Settings','woocommerce-dropshippers'); ?></h3>
                                        <div method="post" action="">
                                            <table>
                                                <tr>    
                                                    <td><label for="dropshipper_paypal_email"><strong><?php echo __('PayPal email','woocommerce-dropshippers'); ?></strong></label></td>
                                                    <td><input type="text" name="dropshipper_paypal_email" value="<?php if($email) echo $email; ?>"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="dropshipper_currency"><strong><?php echo __('Currency','woocommerce-dropshippers'); ?></strong></label></td>
                                                    <td><select name="dropshipper_currency">
                                                        <option value="USD" <?php if($dropshipper_currency=='USD') echo 'selected="selected"'; ?>>US Dollars (&#36;)</option>
                                                        <option value="AUD" <?php if($dropshipper_currency=='AUD') echo 'selected="selected"'; ?>>Australian Dollars (&#36;)</option>
                                                        <option value="BDT" <?php if($dropshipper_currency=='BDT') echo 'selected="selected"'; ?>>Bangladeshi Taka (&#2547;&nbsp;)</option>
                                                        <option value="BRL" <?php if($dropshipper_currency=='BRL') echo 'selected="selected"'; ?>>Brazilian Real (&#82;&#36;)</option>
                                                        <option value="BGN" <?php if($dropshipper_currency=='BGN') echo 'selected="selected"'; ?>>Bulgarian Lev (&#1083;&#1074;.)</option>
                                                        <option value="CAD" <?php if($dropshipper_currency=='CAD') echo 'selected="selected"'; ?>>Canadian Dollars (&#36;)</option>
                                                        <option value="CLP" <?php if($dropshipper_currency=='CLP') echo 'selected="selected"'; ?>>Chilean Peso (&#36;)</option>
                                                        <option value="CNY" <?php if($dropshipper_currency=='CNY') echo 'selected="selected"'; ?>>Chinese Yuan (&yen;)</option>
                                                        <option value="COP" <?php if($dropshipper_currency=='COP') echo 'selected="selected"'; ?>>Colombian Peso (&#36;)</option>
                                                        <option value="CZK" <?php if($dropshipper_currency=='CZK') echo 'selected="selected"'; ?>>Czech Koruna (&#75;&#269;)</option>
                                                        <option value="DKK" <?php if($dropshipper_currency=='DKK') echo 'selected="selected"'; ?>>Danish Krone (&#107;&#114;)</option>
                                                        <option value="EUR" <?php if($dropshipper_currency=='EUR') echo 'selected="selected"'; ?>>Euros (&euro;)</option>
                                                        <option value="HKD" <?php if($dropshipper_currency=='HKD') echo 'selected="selected"'; ?>>Hong Kong Dollar (&#36;)</option>
                                                        <option value="HRK" <?php if($dropshipper_currency=='HRK') echo 'selected="selected"'; ?>>Croatia kuna (Kn)</option>
                                                        <option value="HUF" <?php if($dropshipper_currency=='HUF') echo 'selected="selected"'; ?>>Hungarian Forint (&#70;&#116;)</option>
                                                        <option value="ISK" <?php if($dropshipper_currency=='ISK') echo 'selected="selected"'; ?>>Icelandic krona (Kr.)</option>
                                                        <option value="IDR" <?php if($dropshipper_currency=='IDR') echo 'selected="selected"'; ?>>Indonesia Rupiah (Rp)</option>
                                                        <option value="INR" <?php if($dropshipper_currency=='INR') echo 'selected="selected"'; ?>>Indian Rupee (Rs.)</option>
                                                        <option value="ILS" <?php if($dropshipper_currency=='ILS') echo 'selected="selected"'; ?>>Israeli Shekel (&#8362;)</option>
                                                        <option value="JPY" <?php if($dropshipper_currency=='JPY') echo 'selected="selected"'; ?>>Japanese Yen (&yen;)</option>
                                                        <option value="KRW" <?php if($dropshipper_currency=='KRW') echo 'selected="selected"'; ?>>South Korean Won (&#8361;)</option>
                                                        <option value="MYR" <?php if($dropshipper_currency=='MYR') echo 'selected="selected"'; ?>>Malaysian Ringgits (&#82;&#77;)</option>
                                                        <option value="MXN" <?php if($dropshipper_currency=='MXN') echo 'selected="selected"'; ?>>Mexican Peso (&#36;)</option>
                                                        <option value="NGN" <?php if($dropshipper_currency=='NGN') echo 'selected="selected"'; ?>>Nigerian Naira (&#8358;)</option>
                                                        <option value="NOK" <?php if($dropshipper_currency=='NOK') echo 'selected="selected"'; ?>>Norwegian Krone (&#107;&#114;)</option>
                                                        <option value="NZD" <?php if($dropshipper_currency=='NZD') echo 'selected="selected"'; ?>>New Zealand Dollar (&#36;)</option>
                                                        <option value="PHP" <?php if($dropshipper_currency=='PHP') echo 'selected="selected"'; ?>>Philippine Pesos (&#8369;)</option>
                                                        <option value="PLN" <?php if($dropshipper_currency=='PLN') echo 'selected="selected"'; ?>>Polish Zloty (&#122;&#322;)</option>
                                                        <option value="GBP" <?php if($dropshipper_currency=='GBP') echo 'selected="selected"'; ?>>Pounds Sterling (&pound;)</option>
                                                        <option value="RON" <?php if($dropshipper_currency=='RON') echo 'selected="selected"'; ?>>Romanian Leu (lei)</option>
                                                        <option value="RUB" <?php if($dropshipper_currency=='RUB') echo 'selected="selected"'; ?>>Russian Ruble (&#1088;&#1091;&#1073;.)</option>
                                                        <option value="SGD" <?php if($dropshipper_currency=='SGD') echo 'selected="selected"'; ?>>Singapore Dollar (&#36;)</option>
                                                        <option value="ZAR" <?php if($dropshipper_currency=='ZAR') echo 'selected="selected"'; ?>>South African rand (&#82;)</option>
                                                        <option value="SEK" <?php if($dropshipper_currency=='SEK') echo 'selected="selected"'; ?>>Swedish Krona (&#107;&#114;)</option>
                                                        <option value="CHF" <?php if($dropshipper_currency=='CHF') echo 'selected="selected"'; ?>>Swiss Franc (&#67;&#72;&#70;)</option>
                                                        <option value="TWD" <?php if($dropshipper_currency=='TWD') echo 'selected="selected"'; ?>>Taiwan New Dollars (&#78;&#84;&#36;)</option>
                                                        <option value="THB" <?php if($dropshipper_currency=='THB') echo 'selected="selected"'; ?>>Thai Baht (&#3647;)</option>
                                                        <option value="TRY" <?php if($dropshipper_currency=='TRY') echo 'selected="selected"'; ?>>Turkish Lira (&#84;&#76;)</option>
                                                        <option value="VND" <?php if($dropshipper_currency=='VND') echo 'selected="selected"'; ?>>Vietnamese Dong (&#8363;)</option>
                                                    </select></td>
                                                </tr>
                                            <?php if(isset($options['can_add_droshipping_fee']) && $options['can_add_droshipping_fee']=='Yes'): ?>
                                                <tr>
                                                    <td><label for="dropshipper_country"><strong><?php echo __('Country','woocommerce-dropshippers'); ?></strong></label></td>
                                                    <td><select name="dropshipper_country">
                                                    <?php
                                                        foreach ($countries as $country_code => $country_name) {
                                                            $selected = '';
                                                            if($dropshipper_country == $country_code) $selected = 'selected="selected"';
                                                            echo '<option value="'.$country_code.'" '.$selected.'>'. htmlspecialchars($country_name) .'</option>' . "\n";
                                                        }
                                                    ?>
                                                    </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label for="dropshipper_national_shipping_price"><strong><?php echo str_replace('%SYMBOL%', get_woocommerce_currency_symbol(), __('National shipping price (in shop currency: %SYMBOL%)','woocommerce-dropshippers') ); ?></strong></label></td>
                                                    <td><input type="text" name="dropshipper_national_shipping_price" value="<?php echo $dropshipper_national_shipping_price; ?>"></td>
                                                </tr>
                                                <tr>    
                                                    <td><label for="dropshipper_international_shipping_price"><strong><?php echo str_replace('%SYMBOL%', get_woocommerce_currency_symbol(), __('International shipping price (in shop currency: %SYMBOL%)','woocommerce-dropshippers') ); ?></strong></label></td>
                                                    <td><input type="text" name="dropshipper_international_shipping_price" value="<?php echo $dropshipper_international_shipping_price; ?>"></td>
                                                </tr>
                                            <?php endif; ?>
                                            </table>
                                            <button class="button button-primary dropshippers-save-settings" onclick="saveDropSettings(<?php echo $drop_usr->ID; ?>); return false;"><?php echo __('Save Settings','woocommerce-dropshippers'); ?></button>
                                        </div>
                                    </div>
                                </td>
                                <td colspan="2">
                                    <?php
                                        $csv_column_delimiter = get_user_meta($drop_usr->ID, 'dfm_dropshipper_csv_column_delimiter', true);
                                        if(empty($csv_column_delimiter)){
                                            $csv_column_delimiter = ',';
                                        }
                                        $csv_sku_column_number = get_user_meta($drop_usr->ID, 'dfm_dropshipper_csv_sku_column_number', true);
                                        if(empty($csv_sku_column_number)){
                                            $csv_sku_column_number = 1;
                                        }
                                        $csv_quantity_column_number = get_user_meta($drop_usr->ID, 'dfm_dropshipper_csv_quantity_column_number', true);
                                        if(empty($csv_quantity_column_number)){
                                            $csv_quantity_column_number = 2;
                                        }
                                    ?>
                                    <div class="user-details-content" style="display:none;">
                                        <h3> <?php echo __('Dropshipper CSV Import','woocommerce-dropshippers'); ?></h3>
                                        <form action="" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="single-dropshipper-csv-import" value="1" />
                                            <input type="hidden" name="security" value="<?php echo $csv_nonce; ?>" />
                                            <input type="hidden" name="drop-id" value="<?php echo $drop_usr->ID; ?>" />
                                            <table>
                                                <tr>
                                                    <td><label for="csv-column-delimiter-<?php echo $drop_usr->ID; ?>">CSV column delimiter</label></td>
                                                    <td><input type="text" name="csv-column-delimiter-<?php echo $drop_usr->ID; ?>" id="csv-column-delimiter-<?php echo $drop_usr->ID; ?>" value="<?php echo $csv_column_delimiter; ?>"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="csv-sku-column-<?php echo $drop_usr->ID; ?>">CSV SKU column number</label></td>
                                                    <td><input type="text" name="csv-sku-column-<?php echo $drop_usr->ID; ?>" id="csv-sku-column-<?php echo $drop_usr->ID; ?>" value="<?php echo $csv_sku_column_number; ?>"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="csv-quantity-column-<?php echo $drop_usr->ID; ?>">CSV quantity column number</label></td>
                                                    <td><input type="text" name="csv-quantity-column-<?php echo $drop_usr->ID; ?>" id="csv-quantity-column-<?php echo $drop_usr->ID; ?>" value="<?php echo $csv_quantity_column_number; ?>"></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="csv-file-<?php echo $drop_usr->ID; ?>">CSV File</label></td>
                                                    <td><input type="file" accept="text/csv" name="csv-file-<?php echo $drop_usr->ID; ?>" id="csv-file-<?php echo $drop_usr->ID; ?>"></td>
                                                </tr>
                                            </table>
                                            <button class="button button-primary dropshippers-upload-csv" type="submit"><?php echo __('Import','woocommerce-dropshippers'); ?></button>
                                            <br/><br/><br/><br/>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                    <?php
                        }
                    ?>
                </tbody>
                </table>
                </div>
                <?php
            }
?>