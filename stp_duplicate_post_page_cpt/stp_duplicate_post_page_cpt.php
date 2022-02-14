<?php
/**
 * Plugin Name: STP Duplicate Post Page CPT
 * Plugin URI: https://mytruefalse.site
 * Description: Добавляем кнопку Дублировать для постов, страниц, кастомных записей. За основу взят код с сайта Миши Рудастых: https://misha.agency/wordpress/duplicate-post-and-pages.html?unapproved=7822&moderation-hash=b0f4e1d4eb08a8bc3aef1ee1de2044ad#comment-7822
 * Author: SE
 * Version: 1.0
 * Author URI: https://mytruefalse.site
 */

/*
 * Create post duplicate as draft and redirecting to edit page.
 */
function stp_duplicate_post_as_draft() {
    global $wpdb;
    if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && 'stp_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
        wp_die( 'Нечего дублировать' );
    }
    // get origin post ID
    $post_id = ( isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post'] );
    // and all him data
    $post = get_post( $post_id );
    // if you don't want the current author to be the author of the new post,
    // than replace the next two lines whith: $new_post_author = $post->post_author;
    // when replacing these lines, the author will be copied from the original post;
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;
    // if the post exists, we create a duplicate of it.
    if ( isset( $post ) && $post != null ) {
        // the data array of the new post
        $args = array(
            'comment_status' => $post->comment_status,
            'ping_status' => $post->ping_status,
            'post_author' => $post->post_author,
            'post_content' => $post->post_content,
            'post_excerpt' => $post->excerpt,
            'post_name' => $post->post_name,
            'post_parent' => $post->post_parent,
            'post_password' => $post->post_password,
            'post_status' => 'draft', // if you want to publish - change to publish
            'post_title' => $post->post_title,
            'post_type' => $post->post_type,
            'to_ping' => $post->to_ping,
            'menu_order' => $post->menu_order,
        );
        // create new post using function wp_insert_post;
        $new_post_id = wp_insert_post( $args );
        // assign to new post all elements of taxonomies (categories, tags, etc) from old post;
        $taxonomies = get_object_taxonomies( $post->post_type ); // returns an array of taxonomy names using for the specified post type;
        foreach ( $taxonomies as $taxonomy ) {
            $post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
            wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
        }
        // duplicate all custom fields;
        $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
        if ( count( $post_meta_infos ) != 0 ) {
            $sql_query = "INSERT INTO $wpdb->postmeta( post_id, meta_key, meta_value )";
            foreach ( $post_meta_infos as $meta_info ) {
                $meta_key = $meta_info->meta_key;
                $meta_value = addslashes( $meta_info->meta_value );
                $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
            }
            $sql_query .= implode( " UNION ALL ", $sql_query_sel );
            $wpdb->query( $sql_query );
        }
        // and finally, we redirect the user to the edit page of the new post
        wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
        exit;
    } else {
        wp_die( 'Ошибка создания поста, не могу найти оригинальный пост с ID=: ' . $post_id );
    }
}
add_action( 'admin_action_stp_duplicate_post_as_draft', 'stp_duplicate_post_as_draft' );

/*
 * Adding a duplicate post link for post_raw_actions;
 */
function stp_duplicate_post_link( $actions, $post ) {
    if ( current_user_can( 'edit_posts' ) ) {
        $actions['duplicate'] = '<a href="admin.php?action=stp_duplicate_post_as_draft&post=' . $post->ID . '" title="Дублировать этот пост" rel="permalink">Дублировать</a>';
    }
    return $actions;
}
add_filter( 'post_row_actions', 'stp_duplicate_post_link', 10, 2 );
add_filter( 'page_row_actions', 'stp_duplicate_post_link', 10, 2 );
add_filter( 'library_row_actions', 'stp_duplicate_post_link', 10, 2 );