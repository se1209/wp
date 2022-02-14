<?php
/**
Plugin Name: STP Custom Fields attach file.
Plugin URI: https://mytruefalse.site
Description: Добавляем пользовательские поля. v1.0: прикрепить файл из медиабиблиотеки в проповедях, библиотеке, аудио, видео. За основу взят плагин Ildar Saribzhanov http://sawtech.ru/
Author: SE
Version: 1.4
Author URI: https://mytruefalse.site
*/

/**
 * Connect necessary styles and scripts.
 */
function stp_cf_enqueue_media() {
    // Media API
    wp_enqueue_media();
    // Styles
    wp_enqueue_style( 'attach-one-file-css', plugins_url( '/css/attach-one-file.css', __FILE__ ) );
    // Scripts
    wp_enqueue_script( 'attach-one-file-js', plugins_url( '/js/attach-one-file.js', __FILE__ ), array( 'jquery' ) );
}
add_action( 'admin_enqueue_scripts', 'stp_cf_enqueue_media' );

/**
 * Create metablock "Attach file".
 */
function add_attach_file_meta() {
    add_meta_box( 'add_attach_file_meta', 'Прикрепленный файл', 'add_attach_file_view', array('sermons', 'library', 'audio', 'video') );
}
add_action( 'add_meta_boxes', 'add_attach_file_meta' );

/**
 * HTML attach file metablock.
 */
function add_attach_file_view() {
    global $post;
    // If this post type is different from "sermons",
    // we will leave here,
    // and we will not display anything.
    // UPD 05.07.21
    // After added "library" cpt I commented next three rows,
    // because I don't understand for what their needed.
    //if ($post->post_type != 'sermons') {
    //    return;
    //}

    // Use NONCE for verification.
    wp_nonce_field( plugin_basename( __FILE__ ), 'add_attach_file_nonce' );
    // Getting the value of the attached file.
    $add_file_id = get_post_meta( $post->ID, 'add_file_id', true );
    // Link to add files, if JS is off.
    $upload_link = esc_url( get_upload_iframe_src( 'null', $post->ID ) );
    // Field for choose file.
    echo '
        <div class="custom_field_itm">
            <div class="js-add-wrap">
    ';

    if ($add_file_id) :
        $file_info = get_post( $add_file_id );
        $file_icon = wp_get_attachment_image( $add_file_id, 'thumbnail', true );

        echo '
            <div class="add_file js-add_file_itm">
                <input type="hidden" name="add_file_id" value="' . $add_file_id . '">
                <div class="add_file_icon">' . $file_icon . '</div>
                <p class="add_file_name">' . $file_info->post_title . '</p>
                <a href="#" class="button button-primary button-large js-add-file-remove">Открепить файл</a>
            </div>
        ';
    endif;

    echo '
            </div>
            <br>
            <a href="' . $upload_link . '" class="button button-primary button-large js-add-file">Добавить файл</a>
        </div>
    ';
}

// Save data when post saving.
function stp_cf_save_postdata( $post_id, $post ) {

    // If this post type is different from "sermons",
    // we will leave here,
    // and we will not display anything.
    // UPD 05.07.21
    // After added "library" cpt I commented next three rows,
    // because I don't understand for what their needed.
    //if ( $post->post_type != 'sermons') {
    //    return $post_id;
    //}

    // Check NONCE our page,
    //because save_post maybe calling from different places.
    if ( ! isset( $_POST['add_attach_file_nonce'] ) || ! wp_verify_nonce( $_POST['add_attach_file_nonce'], plugin_basename( __FILE__ ) ) ) {
        return $post_id;
    }
    // Check if that AUTOSAVE, do nothing with the data in our form.
    if ( defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return $post_id;
    }
    // Check whether the user is allowed to specify this data.
    if ( 'page' == $_POST['post_type'] && ! current_user_can( 'edit_page', $post_id ) ) {
        return $post_id;
    } elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }

    // Saving attached file.
    if ( isset( $_POST['add_file_id'] ) ) {
        $add_file_id = (int)$_POST['add_file_id'];
    } else {
        $add_file_id = '';
    }
    update_post_meta( $post_id, 'add_file_id', $add_file_id );

    return $post_id;
}
add_action( 'save_post', 'stp_cf_save_postdata', 10, 2 );