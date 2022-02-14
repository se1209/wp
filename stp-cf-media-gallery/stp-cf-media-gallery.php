<?php
/*
Plugin Name: STP Custom Fields Media Gallery.
Plugin URI: https://mytruefalse.site
Description: Добавляем пользовательское поле Галерея. v1.0: добавляем галерею из медиабиблиотеки в Фотогалерею. За основу взят скрипт этого парня: https://ru.stackoverflow.com/users/268921/%d0%94%d0%b0%d0%bd%d0%b8%d0%b8%d0%bb, с этой страницы: https://ru.stackoverflow.com/questions/740982/%D0%9A%D0%B0%D0%BA-%D1%81%D0%B4%D0%B5%D0%BB%D0%B0%D1%82%D1%8C-%D0%B1%D0%B5%D0%B7-%D0%BF%D0%BB%D0%B0%D0%B3%D0%B8%D0%BD%D0%B0-%D1%82%D0%B0%D0%BA-%D1%87%D1%82%D0%BE%D0%B1%D1%8B-%D0%BC%D0%BE%D0%B6%D0%BD%D0%BE-%D0%B1%D1%8B%D0%BB%D0%BE-%D0%B4%D0%BE%D0%B1%D0%B0%D0%B2%D0%BB%D1%8F%D1%82%D1%8C-%D0%BC%D0%BD%D0%BE%D0%B6%D0%B5%D1%81%D1%82%D0%B2%D0%BE-%D0%BA%D0%B0%D1%80%D1%82%D0%B8%D0%BD%D0%BE%D0%BA-%D0%BA-%D0%BF%D0%BE%D1%81
Author: SE
Version: 1.0
Author URI: https://mytruefalse.ru
 */

/**
 * Connect necessary styles and scripts.
 */
function stp_cf_styles_scripts() {
    wp_enqueue_style( 'cf-media-gallery', plugins_url( '/css/cf-media-gallery.css', __FILE__ ) );
    wp_enqueue_script( 'cf-media-gallery', plugins_url( '/js/cf-media-gallery.js', __FILE__ ), array( 'jquery' ) );
}
add_action( 'admin_enqueue_scripts', 'stp_cf_styles_scripts' );

function add_gallery_images() {
    // ЗДЕСЬ ИЗМЕНЯТЬ ТИП ПОСТА !!!!!!!!!!
    add_meta_box( 'gallery-images', 'Галерея', 'metabox_gallery_images', 'photo-gallery' );
}
add_action( 'add_meta_boxes', 'add_gallery_images' );

function metabox_gallery_images() {
    ?>
    <div id="images-container">
        <ul class="images">
            <?php
            global $post;
            // Здесь meta_type - post.
            // Я думал поставить page, но тогда не работает корректно сохранение.
            if ( metadata_exists( 'post', $post->ID, '_gallery_images' ) ) {
                $gallery_images = get_post_meta( $post->ID, '_gallery_images', true );
            } else {
                $attachment_ids_args = array(
                    'post_parent'       => $post->ID,
                    'numberposts'       => '-1',
                    'post_type'         => 'attachment',
                    'orderby'           => 'menu_order',
                    'order'             => 'ASC',
                    'post_mime_type'    => 'image',
                    'fields'            => 'ids',
                    'meta_value'        => 0,
                );
                $attachment_ids = get_posts($attachment_ids_args);
                $attachment_ids = array_diff( $attachment_ids, array( get_post_thumbnail_id() ) );
                $gallery_images = implode( ',',
                $attachment_ids);
            }
            $attachments = array_filter( explode( ',', $gallery_images ) );

            if ( $attachments ) {
                foreach ( $attachments as $attachment_id ) {
                    echo '
                        <li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '">
                        ' . wp_get_attachment_image( $attachment_id, 'thumbnail' ) . '
                            <ul class="actions">
                                <li>
                                    <a href="#" class="delete tips" data-tip="Удалить картинку">Удалить</a>
                                </li>
                            </ul>
                        </li>
                    ';
                }
            }
            ?>
        </ul>
        <input type="hidden" id="gallery-image" name="gallery-image" value="<?php echo esc_attr( $gallery_images ); ?>">
    </div>
    <p class="add-images hide-if-no-js">
        <a href="#"
            class="button button-primary button-large"
            data-choose="Добавить изображение в галерею"
            data-update="Добавить в галерею"
            data-delete="Удалить изображение"
            data-text="Удалить"
        >Добавить изображения</a>
    </p>
    <?php
}

function update_gallery_images( $post_id, $post, $update ) {
    // ЗДЕСЬ ИЗМЕНЯТЬ ТИП ПОСТА !!!!!!!!!!
    $slug = 'photo-gallery';
    if ( $slug != $post->post_type ) {
        return;
    }
    $attachment_ids = isset( $_POST['gallery-image'] ) ? array_filter( explode( ',', sanitize_text_field( $_POST['gallery-image'] ) ) ) : array();
    update_post_meta( $post_id, '_gallery_images', implode( ',', $attachment_ids ) );
}
add_action( 'save_post', 'update_gallery_images' , 10, 3);

// Такое чувство что эти 3 строчки мне нужны, а этот парень использовал это в каком-то из проектов с товарами, там в его коде было шоп каталог, айкон сингл и др.
// Да, прочитав про функцию add_image_size, понял, что они не нужны.
//add_image_size( '360x240', 360, 240, true );
//add_image_size( '555x370', 555, 370, true );
//add_image_size( '68x45', 68, 45, true );