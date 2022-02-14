<?php
/*
Plugin Name: STP Custom url field.
Plugin URI: https://mytruefalse.site
Description: Добавляем пользовательское поле - url
Author: SE
Version: 1.0
Author URI: https://mytruefalse.site
 */



function stp_cf_url() {
    add_meta_box( 'stp_cf_url', 'Ссылка на сайт/файл с другого сайта', 'add_cf_url_view', 'library' );
}
add_action( 'add_meta_boxes', 'stp_cf_url', 1 );

function add_cf_url_view( $post ) {
    global $post;
    // NONCE
    wp_nonce_field( __FILE__ , 'stp_cf_url_nonce' );
    // Getting the value cf url field
    $stp_cf_url_val = get_post_meta( $post->ID, 'add_url_string', true );
    //beautiful_var_dump($stp_cf_url_val);
    ?>
    <style>
        .wrap-cf-url {
            width: 350px;
        }
        .wrap-cf-url input {
            width: 100%;
        }
    </style>
    <div class="wrap-cf-url">
        <p>
            <input type="url" placeholder="Если нужно, вставляем ссылку сюда" name="add_url_string" value="<?php echo $stp_cf_url_val; ?>">
        </p>
    </div>
    <?php
}

// Save data
add_action( 'save_post', 'stp_cf_url_save_update' );
function stp_cf_url_save_update( $post_id ) {
    //beautiful_var_dump(empty( $_POST['add_url_string'] ));
    // Checking data (nonce, field value)
    if (
        empty( $_POST['add_url_string'] )
        || ! isset( $_POST['add_url_string'] )
        || ! wp_verify_nonce( $_POST['stp_cf_url_nonce'], __FILE__ )
        || wp_is_post_autosave( $post_id )
        || wp_is_post_revision( $post_id )
    )
        return false;

    // Check access rights
    if ( $_POST['post_type'] == 'library' && ! current_user_can( 'edit_page', $post_id ) ) {
        return false;
    } elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
        return false;
    }
    // Saving
    update_post_meta( $post_id, 'add_url_string', $_POST['add_url_string'] );

    return $post_id;
}