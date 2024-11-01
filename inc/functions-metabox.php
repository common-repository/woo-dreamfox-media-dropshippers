<?php
/* METABOX */
        function dfm_print_dropshipper_list_metabox(){
            global $post;
            $woo_dropshipper = get_post_meta( $post->ID, 'dfm_woo_dropshipper', true );
            $dropshipperz = get_users('role=dfm_dropshipper');
            ?>
            <label for="dfm-dropshippers-select"> <?php _e( "Select a Dropshipper",'woocommerce-dropshippers') ?></label>
            <select name="dfm-dropshippers-select" id="dfm-dropshippers-select" style="width:100%;">
                <?php if($woo_dropshipper == null || $woo_dropshipper == '' || $woo_dropshipper == '--'){ ?>
                    <option value="--" selected="selected">-- <?php echo __('No Dropshipper','woocommerce-dropshippers'); ?> --</option>
                <?php } else{ ?>
                    <option value="--">-- <?php echo __('No Dropshipper','woocommerce-dropshippers'); ?> --</option>
                <?php } ?>
            <?php
            if( is_array( $dropshipperz ) && count( $dropshipperz ) > 0 ) {
                foreach ($dropshipperz as $drop) {
                    if($woo_dropshipper == $drop->user_login){
                        echo '<option value="' . $drop->user_login . '" selected="selected">' . ucwords($drop->user_nicename) . '</option>';
                    }
                    else{
                        echo '<option value="' . $drop->user_login . '">' . ucwords($drop->user_nicename) . '</option>';
                    }
                }
            }
            ?>
            </select> 
            <?php
        }
        /* ADD MEDIA UPLOADER IN PLUGIN SETTINGS */
                add_action('admin_enqueue_scripts', 'dfm_woocommerce_dropshippers_enqueue_media');

                function dfm_woocommerce_dropshippers_enqueue_media() {
                    if (isset($_GET['page']) && $_GET['page'] == 'DFM_WooCommerce_Dropshippers') {
                        wp_enqueue_media();
                        wp_register_script('dfm_woocommerce_admin_settings', DFM_URL__JS. '/admin_settings.js', array('jquery'));
                        wp_enqueue_script('dfm_woocommerce_admin_settings');
                    }
                }

        function dfm_add_dropshipper_metaboxes() {
            add_meta_box('dfm_wc_dropshippers_location', __('Dropshipper','woocommerce-dropshippers'), 'dfm_print_dropshipper_list_metabox', 'product', 'side', 'default');
        }

        function dfm_save_dropshipper($post_id, $post){
            // Autosave, do nothing
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
                return $post_id;
            // AJAX? Not used here
            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
                return $post_id;
            // Return if it's a post revision
            if ( false !== wp_is_post_revision( $post_id ) )
                return $post_id;
            if ( $post->post_type !== 'product' ) {
                return;
            }
            /* Get the post type object. */
            $post_type = get_post_type_object( $post->post_type );
            /* Check if the current user has permission to edit the post. */
            if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
                return $post_id;
            /* Get the posted data and sanitize it for use as an HTML class. */
            if(isset( $_POST['dfm-dropshippers-select'])){
                $new_meta_value = $_POST['dfm-dropshippers-select'];//sanitize_html_class( $_POST['dropshippers-select'] );
                update_post_meta( $post_id, 'dfm_woo_dropshipper', $new_meta_value);
            }
        }
?>