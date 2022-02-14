<?php
/**
 * Plugin Name: STP custom fields in Schedule CPT
 * Plugin URI: https://mytruefalse.site
 * Description: Adding times and descriptions duplicating fields.
 * Author: SE
 * Version: 1.1
 * Author URI: https://mytruefalse.site
 */

// Exit if accesed directly
if ( ! defined( 'ABSPATH' ) ) exit();

if ( ! class_exists( 'Schedule_CPT_Custom_Fields' ) ) {

    class Schedule_CPT_Custom_Fields {
        public $post_type = 'stp-schedule';
        static $meta_key = 'stp-schedule-time-and-desc';

        public function __construct() {
            add_action( 'add_meta_boxes', array( $this, 'add_metabox_time_and_description' ) );
            add_action( 'save_post_'.$this->post_type, array( $this, 'save_metabox_time_and_description' ) );
            add_action( 'admin_print_footer_scripts', array( $this, 'show_assets' ), 10, 9 );
        }
        // Add metaboxes.
        public function add_metabox_time_and_description() {
            add_meta_box( 'box_time_and_description', 'Время и описание к нему', array( $this, 'display_metabox_time_and_description' ), $this->post_type );
        }
        // Display metabox time_and_table on edit post page
        public function display_metabox_time_and_description( $post  ) {
            // Use NONCE for verification
            wp_nonce_field( plugin_basename( __FILE__ ), 'time_and_desc_nonce' );
            $times_descriptions = get_post_meta( $post->ID, self::$meta_key, false );

            //log_it($times_descriptions);
            ?>
            <section class="form-time-desc-wrapper flex_box">

                <header>
                    <div class="col col-time">Время</div>
                    <div class="col">Описание</div>
                </header>

                <?php
                if ( ! empty( $times_descriptions[0] ) ) {
                    foreach ( $times_descriptions[0] as $time_and_desc ) {
                        echo '
                            <div class="f-row">
                                <div class="col">
                                    <input type="time" value="'.$time_and_desc[0].'" name="'. self::$meta_key .'_time-divine-service[]" class="col-time">
                                </div>
                                <div class="col">
                                    <textarea rows="3" class="col-desc" name="'. self::$meta_key .'_description-divine-service[]" placeholder="Добавьте описание ко времени">'.$time_and_desc[1].'</textarea>
                                </div>
                                <div class="col">
                                    <button class=" button button-small del-row-time-desc-fields">Удалить</button>
                                </div>
                            </div>
                        ';
                    }
                } else {
                    echo '
                        <div class="f-row">
                            <div class="col">
                                <input type="time" value="18:00" name="'. self::$meta_key .'_time-divine-service[]" class="col-time">
                            </div>
                            <div class="col">
                                <textarea rows="3" class="col-desc" name="'. self::$meta_key .'_description-divine-service[]" placeholder="Добавьте описание ко времени"></textarea>
                            </div>
                            <div class="col">
                                <button class=" button button-small del-row-time-desc-fields">Удалить</button>
                            </div>
                        </div>
                    ';
                }
                ?>

                <a href="" class="button button-primary button-medium add-row-time-desc-fields">Добавить</a>

            </section>

            <?php
        }

        // Clean and save field value
        public function save_metabox_time_and_description( $post_id ) {
            // Check if that AUTOSAVE, do nothing with the data in our form
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

            // Check NONCE our page,
            // because save_post maybe calling from different places.
            if ( ! isset( $_POST['time_and_desc_nonce'] ) || ! wp_verify_nonce( $_POST['time_and_desc_nonce'], plugin_basename( __FILE__ ) ) ) {
                return $post_id;
            }

            // Checking whether the user is allowed ti specify this data.
            if ( $_POST['post_type'] == $this->post_type && ! current_user_can( 'edit_post', $post_id ) ) return $post_id;

            // Далее у Камы проверяется, что отличается ли в $_POST значение нашего поля отличное от нал и храниться ли по этому ключу массив и только тогда идем дальше на обработку этого массива и его сохранение.
            $arr_times = $_POST[self::$meta_key . '_time-divine-service'];
            $arr_descs = $_POST[self::$meta_key . '_description-divine-service'];

            if ( isset( $arr_times ) && isset( $arr_descs ) ) {

                $arr_sorted_times_and_desc = [];

                foreach ($arr_times as $key => $time) {
                    $clean_time = wp_kses_post($time);
                    $clean__desc = wp_kses_post($arr_descs[$key]);
                    $arr_sorted_times_and_desc[] = [$clean_time, $clean__desc];
                }

                log_it($arr_sorted_times_and_desc);
                log_it($arr_sorted_times_and_desc[0]);



                //if ( isset( $_POST[self::$meta_key.''] ) )
                update_post_meta($post_id, self::$meta_key, $arr_sorted_times_and_desc);
            } else {
                delete_post_meta( $post_id, self::$meta_key );
            }
        }

        // Connect CSS adn JS
        public function show_assets() {
            if ( is_admin() && get_current_screen()->id == $this->post_type ) {
                $this->show_styles();
                $this->show_scripts();
            }
        }

        // Display CSS
        public function show_styles() {
            ?>
            <style>
                .flex_box{
                    max-width: max-content;
                }
                header {
                    margin-top: 15px;}
                header, .f-row { display: flex;
                    margin-bottom: 15px; }
                .col {
                    margin-right: 10px; }
                .col-time { width: 114px; }
                .col-desc { width: 250px; }
            </style>
            <?php
        }

        // Display JS
        public function show_scripts() {
            ?>
            <script>
                function duplicateTimeDescFields() {
                    var formTimeDescWrapper = jQuery('.form-time-desc-wrapper');
                    jQuery('.add-row-time-desc-fields', formTimeDescWrapper).click(function (event) {
                        event.preventDefault();
                        let formFieldList = jQuery('.f-row');
                        // Duplicating a row with fields
                        let clonedItemField = formFieldList.first().clone();
                        // Set input value in cloned field
                        clonedItemField.find('input').val('09:00');
                        // Clean textarea value  in cloned field
                        clonedItemField.find('textarea').val('');
                        // Added duplicated fields after last row with fields
                        formFieldList.last().after(clonedItemField);
                    });
                }
                function delTimeDescFields() {
                    var formTimeDescWrapper = jQuery('.form-time-desc-wrapper');
                    formTimeDescWrapper.on('click', '.del-row-time-desc-fields',function (event) {
                        event.preventDefault();
                        let formFieldList = jQuery('.f-row').length;
                        if (formFieldList > 1) {
                            jQuery(this).parent().parent().remove()
                        } else {
                            jQuery(this).parent().parent().find('input').val('09:00');
                        }

                    });
                }
                jQuery(document).ready(function () {
                    duplicateTimeDescFields();
                    delTimeDescFields();
                });
            </script>
            <?php
        }
    }
}

new Schedule_CPT_Custom_Fields();