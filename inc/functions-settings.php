<?php
    /** DROPSHIPPER SETTINGS PAGE **/
        add_action( 'admin_menu', 'dfm_dropshipper_settings_page' );

        function dfm_dropshipper_settings_page() {
            if( ! current_user_can('manage_network') ){

                add_menu_page( __('Dropshipper Settings','woocommerce-dropshippers'), __('Dropshipper Settings','woocommerce-dropshippers'), 'show_dropshipper_widget', 'dfm_dropshipper_settings_page', 'dfm_dropshipper_settings_page_function', 'dashicons-admin-generic', '210.43' );
            }
        }

        function dfm_dropshipper_settings_page_function() {
            if (!current_user_can( 'show_dropshipper_widget' ) ){
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }
            //require_once(sprintf("%s/orders.php", dirname(__FILE__)));
            $user_id = get_current_user_id();
            if(isset($_POST['dropshipper_paypal_email'])){
                $check_all_ok = true;
                $email = sanitize_email($_POST['dropshipper_paypal_email']);
                if(is_email($email, false)){
                    update_user_meta($user_id, 'dfm_dropshipper_paypal_email', $email);
                }
                else{
                    $check_all_ok = false;
                }
                if(isset($_POST['dropshipper_national_shipping_price']) && $_POST['dropshipper_national_shipping_price']!=''){
                    if(is_numeric($_POST['dropshipper_national_shipping_price'])){
                        update_user_meta($user_id, 'dfm_national_shipping_price', $_POST['dropshipper_national_shipping_price']);
                    }
                    else{
                        $check_all_ok = false;
                    }
                }
                if(isset($_POST['dropshipper_international_shipping_price']) && $_POST['dropshipper_international_shipping_price']!='' ){
                    if(is_numeric($_POST['dropshipper_international_shipping_price'])){
                        update_user_meta($user_id, 'dfm_international_shipping_price', $_POST['dropshipper_international_shipping_price']);
                    }
                    else{
                        $check_all_ok = false;
                    }
                }
                if($check_all_ok){ ?>
                    <div id="message" class="updated">
                        <p><strong><?php _e('Settings saved.','woocommerce-dropshippers') ?></strong></p>
                    </div>
                <?php }
                else{ ?>
                    <div id="message" class="error">
                        <p><strong><?php _e('Check your fields.','woocommerce-dropshippers') ?></strong></p>
                    </div>
                <?php }
            }
            $options = get_option('dfm_woocommerce_dropshippers_options');
            $email = get_user_meta($user_id, 'dfm_dropshipper_paypal_email', true);
            $country = get_user_meta($user_id, 'dfm_dropshipper_country', true);
            $national_shipping_price = get_user_meta($user_id, 'dfm_national_shipping_price', true);
            if(empty($national_shipping_price)) $national_shipping_price = 0;
            $international_shipping_price = get_user_meta($user_id, 'dfm_international_shipping_price', true);
            if(empty($international_shipping_price)) $international_shipping_price = 0;
            if(isset($_POST['dropshipper_currency'])){
                $currency = sanitize_text_field($_POST['dropshipper_currency']);
                update_user_meta($user_id, 'dfm_dropshipper_currency', $currency);
            }
            $currency = get_user_meta($user_id, 'dfm_dropshipper_currency', true);
            if(isset($_POST['dropshipper_country'])){
                $country = $_POST['dropshipper_country'];
                update_user_meta($user_id, 'dfm_dropshipper_country', $country);
            }
            $currency = get_user_meta($user_id, 'dfm_dropshipper_currency', true);
            if(!$currency) $currency = 'USD';
            ?>
            <div class="dropshippers-header" style="margin:0; padding:0; width:100%; height:100px; background: url('<?php echo DFM_URL__IMG . '/headerbg.png' ?>'); background-repeat: repeat-x;">
                <img src="<?php echo DFM_URL__IMG . '/woocommerce-dropshippers-header.png' ?>" style="margin:0; padding:0; width:auto; height:100px;">
            </div>
            <?php
            echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
            echo '<h2>'. __('WooCommerce Dropshippers','woocommerce-dropshippers') .'</h2>';
            echo '<h3>'. __('Dropshipper Settings','woocommerce-dropshippers') .'</h3>';
            ?>
            <form method="post" action="">
                <table>
                    <tr>    
                        <td><label for="dropshipper_paypal_email"><strong><?php echo __('PayPal email','woocommerce-dropshippers'); ?></strong></label></td>
                        <td><input type="text" name="dropshipper_paypal_email" value="<?php if($email) echo $email; ?>"></td>
                    </tr>
                    <tr>
                        <td><label for="dropshipper_currency"><strong><?php echo __('Currency','woocommerce-dropshippers'); ?></strong></label></td>
                        <td><select name="dropshipper_currency">
                            <option value="USD" <?php if($currency=='USD') echo 'selected="selected"'; ?>>US Dollars (&#36;)</option>
                            <option value="AUD" <?php if($currency=='AUD') echo 'selected="selected"'; ?>>Australian Dollars (&#36;)</option>
                            <option value="BDT" <?php if($currency=='BDT') echo 'selected="selected"'; ?>>Bangladeshi Taka (&#2547;&nbsp;)</option>
                            <option value="BRL" <?php if($currency=='BRL') echo 'selected="selected"'; ?>>Brazilian Real (&#82;&#36;)</option>
                            <option value="BGN" <?php if($currency=='BGN') echo 'selected="selected"'; ?>>Bulgarian Lev (&#1083;&#1074;.)</option>
                            <option value="CAD" <?php if($currency=='CAD') echo 'selected="selected"'; ?>>Canadian Dollars (&#36;)</option>
                            <option value="CLP" <?php if($currency=='CLP') echo 'selected="selected"'; ?>>Chilean Peso (&#36;)</option>
                            <option value="CNY" <?php if($currency=='CNY') echo 'selected="selected"'; ?>>Chinese Yuan (&yen;)</option>
                            <option value="COP" <?php if($currency=='COP') echo 'selected="selected"'; ?>>Colombian Peso (&#36;)</option>
                            <option value="CZK" <?php if($currency=='CZK') echo 'selected="selected"'; ?>>Czech Koruna (&#75;&#269;)</option>
                            <option value="DKK" <?php if($currency=='DKK') echo 'selected="selected"'; ?>>Danish Krone (&#107;&#114;)</option>
                            <option value="EUR" <?php if($currency=='EUR') echo 'selected="selected"'; ?>>Euros (&euro;)</option>
                            <option value="HKD" <?php if($currency=='HKD') echo 'selected="selected"'; ?>>Hong Kong Dollar (&#36;)</option>
                            <option value="HRK" <?php if($currency=='HRK') echo 'selected="selected"'; ?>>Croatia kuna (Kn)</option>
                            <option value="HUF" <?php if($currency=='HUF') echo 'selected="selected"'; ?>>Hungarian Forint (&#70;&#116;)</option>
                            <option value="ISK" <?php if($currency=='ISK') echo 'selected="selected"'; ?>>Icelandic krona (Kr.)</option>
                            <option value="IDR" <?php if($currency=='IDR') echo 'selected="selected"'; ?>>Indonesia Rupiah (Rp)</option>
                            <option value="INR" <?php if($currency=='INR') echo 'selected="selected"'; ?>>Indian Rupee (Rs.)</option>
                            <option value="ILS" <?php if($currency=='ILS') echo 'selected="selected"'; ?>>Israeli Shekel (&#8362;)</option>
                            <option value="JPY" <?php if($currency=='JPY') echo 'selected="selected"'; ?>>Japanese Yen (&yen;)</option>
                            <option value="KRW" <?php if($currency=='KRW') echo 'selected="selected"'; ?>>South Korean Won (&#8361;)</option>
                            <option value="MYR" <?php if($currency=='MYR') echo 'selected="selected"'; ?>>Malaysian Ringgits (&#82;&#77;)</option>
                            <option value="MXN" <?php if($currency=='MXN') echo 'selected="selected"'; ?>>Mexican Peso (&#36;)</option>
                            <option value="NGN" <?php if($currency=='NGN') echo 'selected="selected"'; ?>>Nigerian Naira (&#8358;)</option>
                            <option value="NOK" <?php if($currency=='NOK') echo 'selected="selected"'; ?>>Norwegian Krone (&#107;&#114;)</option>
                            <option value="NZD" <?php if($currency=='NZD') echo 'selected="selected"'; ?>>New Zealand Dollar (&#36;)</option>
                            <option value="PHP" <?php if($currency=='PHP') echo 'selected="selected"'; ?>>Philippine Pesos (&#8369;)</option>
                            <option value="PLN" <?php if($currency=='PLN') echo 'selected="selected"'; ?>>Polish Zloty (&#122;&#322;)</option>
                            <option value="GBP" <?php if($currency=='GBP') echo 'selected="selected"'; ?>>Pounds Sterling (&pound;)</option>
                            <option value="RON" <?php if($currency=='RON') echo 'selected="selected"'; ?>>Romanian Leu (lei)</option>
                            <option value="RUB" <?php if($currency=='RUB') echo 'selected="selected"'; ?>>Russian Ruble (&#1088;&#1091;&#1073;.)</option>
                            <option value="SGD" <?php if($currency=='SGD') echo 'selected="selected"'; ?>>Singapore Dollar (&#36;)</option>
                            <option value="ZAR" <?php if($currency=='ZAR') echo 'selected="selected"'; ?>>South African rand (&#82;)</option>
                            <option value="SEK" <?php if($currency=='SEK') echo 'selected="selected"'; ?>>Swedish Krona (&#107;&#114;)</option>
                            <option value="CHF" <?php if($currency=='CHF') echo 'selected="selected"'; ?>>Swiss Franc (&#67;&#72;&#70;)</option>
                            <option value="TWD" <?php if($currency=='TWD') echo 'selected="selected"'; ?>>Taiwan New Dollars (&#78;&#84;&#36;)</option>
                            <option value="THB" <?php if($currency=='THB') echo 'selected="selected"'; ?>>Thai Baht (&#3647;)</option>
                            <option value="TRY" <?php if($currency=='TRY') echo 'selected="selected"'; ?>>Turkish Lira (&#84;&#76;)</option>
                            <option value="VND" <?php if($currency=='VND') echo 'selected="selected"'; ?>>Vietnamese Dong (&#8363;)</option>
                        </select></td>
                    </tr>
                    <?php if(isset($options['can_add_droshipping_fee']) && $options['can_add_droshipping_fee']=='Yes'): ?>
                    <?php
                        $countries = apply_filters('woocommerce_countries', array(
                            'AF' => __( 'Afghanistan', 'woocommerce' ),
                            'AX' => __( 'Åland Islands', 'woocommerce' ),
                            'AL' => __( 'Albania', 'woocommerce' ),
                            'DZ' => __( 'Algeria', 'woocommerce' ),
                            'AD' => __( 'Andorra', 'woocommerce' ),
                            'AO' => __( 'Angola', 'woocommerce' ),
                            'AI' => __( 'Anguilla', 'woocommerce' ),
                            'AQ' => __( 'Antarctica', 'woocommerce' ),
                            'AG' => __( 'Antigua and Barbuda', 'woocommerce' ),
                            'AR' => __( 'Argentina', 'woocommerce' ),
                            'AM' => __( 'Armenia', 'woocommerce' ),
                            'AW' => __( 'Aruba', 'woocommerce' ),
                            'AU' => __( 'Australia', 'woocommerce' ),
                            'AT' => __( 'Austria', 'woocommerce' ),
                            'AZ' => __( 'Azerbaijan', 'woocommerce' ),
                            'BS' => __( 'Bahamas', 'woocommerce' ),
                            'BH' => __( 'Bahrain', 'woocommerce' ),
                            'BD' => __( 'Bangladesh', 'woocommerce' ),
                            'BB' => __( 'Barbados', 'woocommerce' ),
                            'BY' => __( 'Belarus', 'woocommerce' ),
                            'BE' => __( 'Belgium', 'woocommerce' ),
                            'PW' => __( 'Belau', 'woocommerce' ),
                            'BZ' => __( 'Belize', 'woocommerce' ),
                            'BJ' => __( 'Benin', 'woocommerce' ),
                            'BM' => __( 'Bermuda', 'woocommerce' ),
                            'BT' => __( 'Bhutan', 'woocommerce' ),
                            'BO' => __( 'Bolivia', 'woocommerce' ),
                            'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'woocommerce' ),
                            'BA' => __( 'Bosnia and Herzegovina', 'woocommerce' ),
                            'BW' => __( 'Botswana', 'woocommerce' ),
                            'BV' => __( 'Bouvet Island', 'woocommerce' ),
                            'BR' => __( 'Brazil', 'woocommerce' ),
                            'IO' => __( 'British Indian Ocean Territory', 'woocommerce' ),
                            'VG' => __( 'British Virgin Islands', 'woocommerce' ),
                            'BN' => __( 'Brunei', 'woocommerce' ),
                            'BG' => __( 'Bulgaria', 'woocommerce' ),
                            'BF' => __( 'Burkina Faso', 'woocommerce' ),
                            'BI' => __( 'Burundi', 'woocommerce' ),
                            'KH' => __( 'Cambodia', 'woocommerce' ),
                            'CM' => __( 'Cameroon', 'woocommerce' ),
                            'CA' => __( 'Canada', 'woocommerce' ),
                            'CV' => __( 'Cape Verde', 'woocommerce' ),
                            'KY' => __( 'Cayman Islands', 'woocommerce' ),
                            'CF' => __( 'Central African Republic', 'woocommerce' ),
                            'TD' => __( 'Chad', 'woocommerce' ),
                            'CL' => __( 'Chile', 'woocommerce' ),
                            'CN' => __( 'China', 'woocommerce' ),
                            'CX' => __( 'Christmas Island', 'woocommerce' ),
                            'CC' => __( 'Cocos (Keeling) Islands', 'woocommerce' ),
                            'CO' => __( 'Colombia', 'woocommerce' ),
                            'KM' => __( 'Comoros', 'woocommerce' ),
                            'CG' => __( 'Congo (Brazzaville)', 'woocommerce' ),
                            'CD' => __( 'Congo (Kinshasa)', 'woocommerce' ),
                            'CK' => __( 'Cook Islands', 'woocommerce' ),
                            'CR' => __( 'Costa Rica', 'woocommerce' ),
                            'HR' => __( 'Croatia', 'woocommerce' ),
                            'CU' => __( 'Cuba', 'woocommerce' ),
                            'CW' => __( 'Cura&Ccedil;ao', 'woocommerce' ),
                            'CY' => __( 'Cyprus', 'woocommerce' ),
                            'CZ' => __( 'Czech Republic', 'woocommerce' ),
                            'DK' => __( 'Denmark', 'woocommerce' ),
                            'DJ' => __( 'Djibouti', 'woocommerce' ),
                            'DM' => __( 'Dominica', 'woocommerce' ),
                            'DO' => __( 'Dominican Republic', 'woocommerce' ),
                            'EC' => __( 'Ecuador', 'woocommerce' ),
                            'EG' => __( 'Egypt', 'woocommerce' ),
                            'SV' => __( 'El Salvador', 'woocommerce' ),
                            'GQ' => __( 'Equatorial Guinea', 'woocommerce' ),
                            'ER' => __( 'Eritrea', 'woocommerce' ),
                            'EE' => __( 'Estonia', 'woocommerce' ),
                            'ET' => __( 'Ethiopia', 'woocommerce' ),
                            'FK' => __( 'Falkland Islands', 'woocommerce' ),
                            'FO' => __( 'Faroe Islands', 'woocommerce' ),
                            'FJ' => __( 'Fiji', 'woocommerce' ),
                            'FI' => __( 'Finland', 'woocommerce' ),
                            'FR' => __( 'France', 'woocommerce' ),
                            'GF' => __( 'French Guiana', 'woocommerce' ),
                            'PF' => __( 'French Polynesia', 'woocommerce' ),
                            'TF' => __( 'French Southern Territories', 'woocommerce' ),
                            'GA' => __( 'Gabon', 'woocommerce' ),
                            'GM' => __( 'Gambia', 'woocommerce' ),
                            'GE' => __( 'Georgia', 'woocommerce' ),
                            'DE' => __( 'Germany', 'woocommerce' ),
                            'GH' => __( 'Ghana', 'woocommerce' ),
                            'GI' => __( 'Gibraltar', 'woocommerce' ),
                            'GR' => __( 'Greece', 'woocommerce' ),
                            'GL' => __( 'Greenland', 'woocommerce' ),
                            'GD' => __( 'Grenada', 'woocommerce' ),
                            'GP' => __( 'Guadeloupe', 'woocommerce' ),
                            'GT' => __( 'Guatemala', 'woocommerce' ),
                            'GG' => __( 'Guernsey', 'woocommerce' ),
                            'GN' => __( 'Guinea', 'woocommerce' ),
                            'GW' => __( 'Guinea-Bissau', 'woocommerce' ),
                            'GY' => __( 'Guyana', 'woocommerce' ),
                            'HT' => __( 'Haiti', 'woocommerce' ),
                            'HM' => __( 'Heard Island and McDonald Islands', 'woocommerce' ),
                            'HN' => __( 'Honduras', 'woocommerce' ),
                            'HK' => __( 'Hong Kong', 'woocommerce' ),
                            'HU' => __( 'Hungary', 'woocommerce' ),
                            'IS' => __( 'Iceland', 'woocommerce' ),
                            'IN' => __( 'India', 'woocommerce' ),
                            'ID' => __( 'Indonesia', 'woocommerce' ),
                            'IR' => __( 'Iran', 'woocommerce' ),
                            'IQ' => __( 'Iraq', 'woocommerce' ),
                            'IE' => __( 'Republic of Ireland', 'woocommerce' ),
                            'IM' => __( 'Isle of Man', 'woocommerce' ),
                            'IL' => __( 'Israel', 'woocommerce' ),
                            'IT' => __( 'Italy', 'woocommerce' ),
                            'CI' => __( 'Ivory Coast', 'woocommerce' ),
                            'JM' => __( 'Jamaica', 'woocommerce' ),
                            'JP' => __( 'Japan', 'woocommerce' ),
                            'JE' => __( 'Jersey', 'woocommerce' ),
                            'JO' => __( 'Jordan', 'woocommerce' ),
                            'KZ' => __( 'Kazakhstan', 'woocommerce' ),
                            'KE' => __( 'Kenya', 'woocommerce' ),
                            'KI' => __( 'Kiribati', 'woocommerce' ),
                            'KW' => __( 'Kuwait', 'woocommerce' ),
                            'KG' => __( 'Kyrgyzstan', 'woocommerce' ),
                            'LA' => __( 'Laos', 'woocommerce' ),
                            'LV' => __( 'Latvia', 'woocommerce' ),
                            'LB' => __( 'Lebanon', 'woocommerce' ),
                            'LS' => __( 'Lesotho', 'woocommerce' ),
                            'LR' => __( 'Liberia', 'woocommerce' ),
                            'LY' => __( 'Libya', 'woocommerce' ),
                            'LI' => __( 'Liechtenstein', 'woocommerce' ),
                            'LT' => __( 'Lithuania', 'woocommerce' ),
                            'LU' => __( 'Luxembourg', 'woocommerce' ),
                            'MO' => __( 'Macao S.A.R., China', 'woocommerce' ),
                            'MK' => __( 'Macedonia', 'woocommerce' ),
                            'MG' => __( 'Madagascar', 'woocommerce' ),
                            'MW' => __( 'Malawi', 'woocommerce' ),
                            'MY' => __( 'Malaysia', 'woocommerce' ),
                            'MV' => __( 'Maldives', 'woocommerce' ),
                            'ML' => __( 'Mali', 'woocommerce' ),
                            'MT' => __( 'Malta', 'woocommerce' ),
                            'MH' => __( 'Marshall Islands', 'woocommerce' ),
                            'MQ' => __( 'Martinique', 'woocommerce' ),
                            'MR' => __( 'Mauritania', 'woocommerce' ),
                            'MU' => __( 'Mauritius', 'woocommerce' ),
                            'YT' => __( 'Mayotte', 'woocommerce' ),
                            'MX' => __( 'Mexico', 'woocommerce' ),
                            'FM' => __( 'Micronesia', 'woocommerce' ),
                            'MD' => __( 'Moldova', 'woocommerce' ),
                            'MC' => __( 'Monaco', 'woocommerce' ),
                            'MN' => __( 'Mongolia', 'woocommerce' ),
                            'ME' => __( 'Montenegro', 'woocommerce' ),
                            'MS' => __( 'Montserrat', 'woocommerce' ),
                            'MA' => __( 'Morocco', 'woocommerce' ),
                            'MZ' => __( 'Mozambique', 'woocommerce' ),
                            'MM' => __( 'Myanmar', 'woocommerce' ),
                            'NA' => __( 'Namibia', 'woocommerce' ),
                            'NR' => __( 'Nauru', 'woocommerce' ),
                            'NP' => __( 'Nepal', 'woocommerce' ),
                            'NL' => __( 'Netherlands', 'woocommerce' ),
                            'AN' => __( 'Netherlands Antilles', 'woocommerce' ),
                            'NC' => __( 'New Caledonia', 'woocommerce' ),
                            'NZ' => __( 'New Zealand', 'woocommerce' ),
                            'NI' => __( 'Nicaragua', 'woocommerce' ),
                            'NE' => __( 'Niger', 'woocommerce' ),
                            'NG' => __( 'Nigeria', 'woocommerce' ),
                            'NU' => __( 'Niue', 'woocommerce' ),
                            'NF' => __( 'Norfolk Island', 'woocommerce' ),
                            'KP' => __( 'North Korea', 'woocommerce' ),
                            'NO' => __( 'Norway', 'woocommerce' ),
                            'OM' => __( 'Oman', 'woocommerce' ),
                            'PK' => __( 'Pakistan', 'woocommerce' ),
                            'PS' => __( 'Palestinian Territory', 'woocommerce' ),
                            'PA' => __( 'Panama', 'woocommerce' ),
                            'PG' => __( 'Papua New Guinea', 'woocommerce' ),
                            'PY' => __( 'Paraguay', 'woocommerce' ),
                            'PE' => __( 'Peru', 'woocommerce' ),
                            'PH' => __( 'Philippines', 'woocommerce' ),
                            'PN' => __( 'Pitcairn', 'woocommerce' ),
                            'PL' => __( 'Poland', 'woocommerce' ),
                            'PT' => __( 'Portugal', 'woocommerce' ),
                            'QA' => __( 'Qatar', 'woocommerce' ),
                            'RE' => __( 'Reunion', 'woocommerce' ),
                            'RO' => __( 'Romania', 'woocommerce' ),
                            'RU' => __( 'Russia', 'woocommerce' ),
                            'RW' => __( 'Rwanda', 'woocommerce' ),
                            'BL' => __( 'Saint Barth&eacute;lemy', 'woocommerce' ),
                            'SH' => __( 'Saint Helena', 'woocommerce' ),
                            'KN' => __( 'Saint Kitts and Nevis', 'woocommerce' ),
                            'LC' => __( 'Saint Lucia', 'woocommerce' ),
                            'MF' => __( 'Saint Martin (French part)', 'woocommerce' ),
                            'SX' => __( 'Saint Martin (Dutch part)', 'woocommerce' ),
                            'PM' => __( 'Saint Pierre and Miquelon', 'woocommerce' ),
                            'VC' => __( 'Saint Vincent and the Grenadines', 'woocommerce' ),
                            'SM' => __( 'San Marino', 'woocommerce' ),
                            'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'woocommerce' ),
                            'SA' => __( 'Saudi Arabia', 'woocommerce' ),
                            'SN' => __( 'Senegal', 'woocommerce' ),
                            'RS' => __( 'Serbia', 'woocommerce' ),
                            'SC' => __( 'Seychelles', 'woocommerce' ),
                            'SL' => __( 'Sierra Leone', 'woocommerce' ),
                            'SG' => __( 'Singapore', 'woocommerce' ),
                            'SK' => __( 'Slovakia', 'woocommerce' ),
                            'SI' => __( 'Slovenia', 'woocommerce' ),
                            'SB' => __( 'Solomon Islands', 'woocommerce' ),
                            'SO' => __( 'Somalia', 'woocommerce' ),
                            'ZA' => __( 'South Africa', 'woocommerce' ),
                            'GS' => __( 'South Georgia/Sandwich Islands', 'woocommerce' ),
                            'KR' => __( 'South Korea', 'woocommerce' ),
                            'SS' => __( 'South Sudan', 'woocommerce' ),
                            'ES' => __( 'Spain', 'woocommerce' ),
                            'LK' => __( 'Sri Lanka', 'woocommerce' ),
                            'SD' => __( 'Sudan', 'woocommerce' ),
                            'SR' => __( 'Suriname', 'woocommerce' ),
                            'SJ' => __( 'Svalbard and Jan Mayen', 'woocommerce' ),
                            'SZ' => __( 'Swaziland', 'woocommerce' ),
                            'SE' => __( 'Sweden', 'woocommerce' ),
                            'CH' => __( 'Switzerland', 'woocommerce' ),
                            'SY' => __( 'Syria', 'woocommerce' ),
                            'TW' => __( 'Taiwan', 'woocommerce' ),
                            'TJ' => __( 'Tajikistan', 'woocommerce' ),
                            'TZ' => __( 'Tanzania', 'woocommerce' ),
                            'TH' => __( 'Thailand', 'woocommerce' ),
                            'TL' => __( 'Timor-Leste', 'woocommerce' ),
                            'TG' => __( 'Togo', 'woocommerce' ),
                            'TK' => __( 'Tokelau', 'woocommerce' ),
                            'TO' => __( 'Tonga', 'woocommerce' ),
                            'TT' => __( 'Trinidad and Tobago', 'woocommerce' ),
                            'TN' => __( 'Tunisia', 'woocommerce' ),
                            'TR' => __( 'Turkey', 'woocommerce' ),
                            'TM' => __( 'Turkmenistan', 'woocommerce' ),
                            'TC' => __( 'Turks and Caicos Islands', 'woocommerce' ),
                            'TV' => __( 'Tuvalu', 'woocommerce' ),
                            'UG' => __( 'Uganda', 'woocommerce' ),
                            'UA' => __( 'Ukraine', 'woocommerce' ),
                            'AE' => __( 'United Arab Emirates', 'woocommerce' ),
                            'GB' => __( 'United Kingdom', 'woocommerce' ),
                            'US' => __( 'United States', 'woocommerce' ),
                            'UY' => __( 'Uruguay', 'woocommerce' ),
                            'UZ' => __( 'Uzbekistan', 'woocommerce' ),
                            'VU' => __( 'Vanuatu', 'woocommerce' ),
                            'VA' => __( 'Vatican', 'woocommerce' ),
                            'VE' => __( 'Venezuela', 'woocommerce' ),
                            'VN' => __( 'Vietnam', 'woocommerce' ),
                            'WF' => __( 'Wallis and Futuna', 'woocommerce' ),
                            'EH' => __( 'Western Sahara', 'woocommerce' ),
                            'WS' => __( 'Western Samoa', 'woocommerce' ),
                            'YE' => __( 'Yemen', 'woocommerce' ),
                            'ZM' => __( 'Zambia', 'woocommerce' ),
                            'ZW' => __( 'Zimbabwe', 'woocommerce' )
                        ));
                        if(empty($country)){
                            $country = 'US';
                        }
                    ?>
                    <tr>
                        <td><label for="dropshipper_country"><strong><?php echo __('Country','woocommerce-dropshippers'); ?></strong></label></td>
                        <td><select name="dropshipper_country">
                        <?php
                            foreach ($countries as $country_code => $country_name) {
                                $selected = '';
                                if($country == $country_code) $selected = 'selected="selected"';
                                echo '<option value="'.$country_code.'" '.$selected.'>'. htmlspecialchars($country_name) .'</option>' . "\n";
                            }
                        ?>
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="dropshipper_national_shipping_price"><strong><?php echo str_replace('%SYMBOL%', get_woocommerce_currency_symbol(), __('National shipping price (in shop currency: %SYMBOL%)','woocommerce-dropshippers') ); ?></strong></label></td>
                        <td><input type="text" name="dropshipper_national_shipping_price" value="<?php echo $national_shipping_price; ?>"></td>
                    </tr>
                    <tr>    
                        <td><label for="dropshipper_international_shipping_price"><strong><?php echo str_replace('%SYMBOL%', get_woocommerce_currency_symbol(), __('International shipping price (in shop currency: %SYMBOL%)','woocommerce-dropshippers') ); ?></strong></label></td>
                        <td><input type="text" name="dropshipper_international_shipping_price" value="<?php echo $international_shipping_price; ?>"></td>
                    </tr>
                    <?php endif; ?>
                </table>
                <?php submit_button(__('Save Settings','woocommerce-dropshippers')); ?>
            </form>
            <?php
        }
?>