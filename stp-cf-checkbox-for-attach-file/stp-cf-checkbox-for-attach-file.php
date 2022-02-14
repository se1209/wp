<?php
/**
 * Plugin Name: STP custom field checkbox for attached files.
 * Plugin URI: https://mytruefalse.site
 * Description: Добавляем пользовательское поле: чекбокс для прикрепленного файла. В версии 1 для видео.
 * Author: SE
 * Version: 1.0
 * Author URI: https://mytruefalse.site
 */

/**
 * Create metablock "checkbox for attached files"
 */
function add_checkbox_for_attached_file_meta() {
    add_meta_box( 'add_checkbox_for_attached_file_meta', 'Варианты использования прикрепленного файла', 'add_checkbox_for_attached_file_view', array('video') );
}
add_action( 'add_meta_boxes', 'add_checkbox_for_attached_file_meta' );

/**
 * HTML "checkbox for attached file" metablock
 */
function add_checkbox_for_attached_file_view() {
    global $post;
    wp_nonce_field( plugin_basename( __FILE__ ), 'add_checkbox_for_attached_file_nonce' );
    echo '
    <style>
    .color-red { color: red; }
    .color-green { color: green; }
    .color-orange { color: orange; }
</style>
    ';

    // Здесь нужно проверить было ли загружено видео к записи.
    // Т.е. Проверяем загрузку и расширение файла.
    // ID загруженного файла.
    $added_file_id = get_post_meta( $post->ID, 'add_file_id', true );
    // Метаданные загруженного файла.
    $added_file_metadata = wp_get_attachment_metadata( $added_file_id );
    // Сообщение о ошибочном расширении.
    $msg_wrong_extension = '
        <p class="color-red">Загружен файл не соответствующего расширения!</p>
        <p>Расширение файла должно быть: .mp4, .avi</p>
        <p>После добавления, не забудьте обновить (сохранить) данные!</p>
    ';

    // Файл добавлен?
    if ( !empty( $added_file_id ) ) {

        // Есть ли ключ в массиве со значением расширения "fileformat"
        if ( !array_key_exists('fileformat', $added_file_metadata) ) {
            echo $msg_wrong_extension;
            return false;
        }

        // Это видео .avi или .mp4?
        if ( $added_file_metadata['fileformat'] == 'mp4' || $added_file_metadata['fileformat'] == 'avi' ) {


            // Работаем с чекбоксами.
            // Use NONCE for verification
            wp_nonce_field( plugin_basename( __FILE__ ), 'add_checkbox_for_attached_file_nonce' );

            $checkbox_option_for_attached_file = get_post_meta( $post->ID, 'checkbox_option_for_attached_file', true );

            //Messages
            $msg_checkbox_view_not_display = '<div><p>Опция "Просмотр" неактивна т.к. видео загружено с расширением ".AVI", такие видео в браузере просмотреть нельзя!</p></div>';

            // Из БД получаем или массив или пустую строку.

            // Если массив и в нём что-то есть показываем заполненные чекбоксы
            if ( is_array( $checkbox_option_for_attached_file ) || ! empty( $checkbox_option_for_attached_file ) ) {

                $checkbox_option_for_attached_file_View = '';
                $checkbox_option_for_attached_file_Download = '';

                foreach ( $checkbox_option_for_attached_file as $checkbox_value ) {

                    // Теперь проходим по сохраненным чекбоксам и если нужный чекбокс сохранен
                    // нужно лобавить галочку на него.
                    if ($checkbox_value == 'item-view') {
                        $checkbox_option_for_attached_file_View = 'checked';
                    } elseif ($checkbox_value == 'item-download') {
                        $checkbox_option_for_attached_file_Download = 'checked';
                    }
                }
                // For .AVI extension checkbox VIEW option don't display,
                // because .AVI is not displayed in browsers.
                if ( $added_file_metadata['fileformat'] != 'avi' ) {
                    echo '<div><p><input type="checkbox" name="checkbox_option_for_attached_file[]" value="item-view" '. $checkbox_option_for_attached_file_View .'>Просмотр</p></div>';
                } else {
                    echo $msg_checkbox_view_not_display;
                }
                echo '<div><p><input type="checkbox" name="checkbox_option_for_attached_file[]" value="item-download" '. $checkbox_option_for_attached_file_Download .'>Скачивание</p></div>';

            } else {
                // Если пустой что показываем не заполненные чекбоксы.
                // For .AVI extension checkbox VIEW option don't display,
                // because .AVI is not displayed in browsers.
                if ( $added_file_metadata['fileformat'] != 'avi' ) {
                    echo '<div><p><input type="checkbox" name="checkbox_option_for_attached_file[]" value="item-view">Просмотр</p></div>';
                } else {
                    echo $msg_checkbox_view_not_display;
                }
                echo '<div><p><input type="checkbox" name="checkbox_option_for_attached_file[]" value="item-download">Скачивание</p></div>';
            }

        } else {
            echo $msg_wrong_extension;
        }
    } else {
        echo '
            <p class="color-green">Для выбора варианта отображения добавьте файл в блоке "Прикрепленный файл" выше и обновите* запись!</p>
            <p>Расширение файла должно быть: .mp4, .avi</p>
            <p>После добавления, не забудьте обновить/сохранить* данные!</p>
            <p>*Кнопка "Обновить" справа вверху ;-)</p>
        ';
    }
}

// Сохраняем данные когда сохраняем запись.
function add_checkbox_for_attached_file_save( $post_id, $post ) {

    // Check if that AUTOSAVE, do nothing with the data in our form.
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

    // Check NONCE our page,
    //because save_post maybe calling from different places.
    if ( ! isset( $_POST['add_checkbox_for_attached_file_nonce'] ) || ! wp_verify_nonce( $_POST['add_checkbox_for_attached_file_nonce'], plugin_basename( __FILE__ ) ) ) {
        return $post_id;
    }

    // Check whether the user is allowed to specify this data.
    if( !current_user_can( 'edit_post' ) ) return $post_id;

    // Save checkbox option for attached file
    if ( isset( $_POST['checkbox_option_for_attached_file'] ) ) {
        $checkbox_option_for_attached_file = $_POST['checkbox_option_for_attached_file'];
    } else {
        $checkbox_option_for_attached_file = '';
    }

    update_post_meta( $post_id, 'checkbox_option_for_attached_file', $checkbox_option_for_attached_file );

    return $post_id;
}
add_action( 'save_post', 'add_checkbox_for_attached_file_save', 10, 2 );