<?php

/**
 *
 * Plugin Name:       WHA Puzzle
 * Description:       The plugin puzzle.
 * Version:           1.0.9
 * Author:            WHA
 * Author URI:        http://webhelpagency.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wha-puzzle
 * Domain Path:       /languages
 *
 * WHA Puzzle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WHA puzzle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WHA Password. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
 */
 


if (!defined('ABSPATH')) {
    exit;
}

/**
 * If this file is called directly, abort.
 */
if ( !defined('WPINC') ) {
    exit;
}

define( 'WHA_PUZZLE_VERSION', '1.0.6' );

function wha_puzzle_activation(){}

register_activation_hook(__FILE__, 'wha_puzzle_activation');

function wha_puzzle_deactivation(){}

register_deactivation_hook(__FILE__, 'wha_puzzle_deactivation');

/**
 * Add scripts & styles admin panel
 */
if( ! function_exists('whapz_action_admin' ) ) {

    add_action('admin_enqueue_scripts', 'whapz_action_admin');

    function whapz_action_admin() {

        global $post;

        wp_enqueue_style('whapz-style-admin', plugins_url('css/main.min.css', __FILE__));
        wp_enqueue_script('whapz-script-admin', plugins_url('admin/js/puzzle-admin.js', __FILE__), false, '1.0.0', true);
        wp_enqueue_script('whapz-piker-admin', plugins_url('admin/js/jscolor.js', __FILE__), false, '1.0', true);
    }
}


/**
 * Register scripts & styles frontend
 */
if( ! function_exists('whapz_setup_script' ) ) {

    add_action('wp_enqueue_scripts', 'whapz_setup_script');
    function whapz_setup_script() {

        global $post;

        wp_enqueue_media();

        wp_enqueue_style('puzzle-style', plugins_url('css/main.min.css', __FILE__));

        if (is_rtl()) {
            wp_enqueue_style('puzzle-style-rtl', plugins_url('css/rtl-puzzle.css', __FILE__));
        }

        wp_enqueue_script('whapz-puzzle-cookie', plugins_url('js/jquery.cookie.js', __FILE__), array('jquery'), false, '1.0', true);
        wp_enqueue_script('whapz-puzzle-create', plugins_url('js/createjs_1.1_min.js', __FILE__), false, false, '1.0', true);
        wp_enqueue_script('whapz-puzzle-zim', plugins_url('js/zim_6.9.0.js', __FILE__), false, false, '1.0', true);
        wp_enqueue_script('whapz-puzzle-script', plugins_url('js/puzzle.js', __FILE__), array('jquery'), '1.0.0', '1.0', true);
    }
}


/**
 * Initialization post type wha-puzzle
 */
if( ! function_exists('whapz_register_post_type' ) ) {

    add_action('init', 'whapz_register_post_type');

    function whapz_register_post_type() {

        $labels = array(
            'name'               => __('Puzzles', 'whapz_puzzle'),
            'menu_name'          => __('Puzzle', 'whapz_puzzle'),
            'singular_name'      => __('Puzzle', 'whapz_puzzle'),
            'name_admin_bar'     => __('Puzzle', 'name admin bar', 'whapz_puzzle'),
            'all_items'          => __('All  Puzzles', 'whapz_puzzle'),
            'search_items'       => __('Search  Puzzles', 'whapz_puzzle'),
            'add_new'            => __('Add New', 'puzzle', 'whapz_puzzle'),
            'add_new_item'       => __('Add New Puzzle', 'whapz_puzzle'),
            'new_item'           => __('New  Puzzle', 'whapz_puzzle'),
            'view_item'          => __('View  Puzzle', 'whapz_puzzle'),
            'edit_item'          => __('Edit  Puzzle', 'whapz_puzzle'),
            'not_found'          => __('No  Puzzle Found.', 'whapz_puzzle'),
            'not_found_in_trash' => __('Puzzle not found in Trash.', 'whapz_puzzle'),
            'parent_item_colon'  => __('Parent Puzzle', 'whapz_puzzle'),
        );

        $args = array(
            'labels' => $labels,
            'description'   => __('Holds the puzzle and their data.', 'whapz_puzzle'),
            'menu_position' => 5,
            'menu_icon'     => 'dashicons-editor-help',
            'public'        => false,
            'publicly_queryable' => false,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'query_var'     => true,
            'capability_type' => 'post',
            'has_archive'   => true,
            'hierarchical'  => false,
            'supports'      => array('title', 'thumbnail'),
        );

        register_post_type('wha_puzzle', $args);

        // Rename file
        add_filter( 'sanitize_file_name', 'whapz_translit', 1 );

        // Add new size images
        add_image_size( 'desktop-puzzle-image', 800, 600, true );
        add_image_size( 'mobile-puzzle-image', 320, 320);

        //support post thumbnails
        add_theme_support( 'post-thumbnails',array('wha_puzzle'));
    }
}


/**
 * Create puzzle page
 */
if( ! function_exists('whapz_create_puzzle_page' ) ) {

    add_filter('the_content', 'whapz_create_puzzle_page');

    function whapz_create_puzzle_page($content) {

        global $post;

        if ('wha_puzzle' !== $post->post_type) {
            return $content;
        }

        if (!is_single()) {
            return $content;
        }

        $puzzle_html = whapz_whagamepuzzle_func(array('id' => $post->ID));

        return $puzzle_html . $content;
    }
}


/**
 * Create shortcode puzzle
 */
if( ! function_exists('whapz_whagamepuzzle_func' ) ) {

    function whapz_whagamepuzzle_func($atts) {

        if (!isset($atts['id'])) {
            return false;
        }
        $id = $atts['id'];

        $html = '';
        $html .= '<div id="whapz-puzzle">';
        $html .= '<div id="puzzleID-'.$id.'"></div>';

        $cpu = !empty(get_post_meta($id, 'whapz_control_panel_upload', true)) ? true : false;

        $html .= "<div class='whapz-panel-puzzle whapz-small'>
                      <a id='whapz-button-normal' title='Full screen' href='javascript:wha_screen(\"full\");'>
                      <img src='". plugins_url('images/icon-full-screen.png', __FILE__)."'></a>                      
                      <div class='whapz-timer-wrap'> 
                          <div class='whapz-group-buttons'>
                              <input class='whapz-startButton' type='button' title='Start game'>                                          
                              <input class='whapz-resetButton' type='button' title='Reset game'>                             
                          </div>   
                          <div class='whapz-timer-item'>
                            <span class='whapz-min'>00</span>:<span class='whapz-sec'>00</span>
                          </div>                          
                      </div>";

        if( $cpu ):

        $html .= " <div class='whapz-form-upload'>
                      <form id='formElem' enctype='multipart/form-data'>
                        <input type='file' name='imagefile' id='imagefile' class='whapz-inputfile' />                        
                        <label for='imagefile'>Choose Image</label> 
                        <input type='hidden' name='id_post' id='id_post' value='".$id."'>
                        <input class='whapz-add-image' name='add-image' type='button' value='Add Image'>                       
                      </form>
                   </div>";
        endif;

        $html .=    "<div class='whapz-completed'>".__('Completed:','whapz_puzzle')." <span></span></div></div>";

        $html .= "<script>
        /* <![CDATA[ */
        var optional_puzzle_vars = {
            'whapz_horizontal_pieces':'" . get_post_meta($id, 'whapz_option_horizontal_pieces', true) . "',
            'whapz_vertical_pieces':'" . get_post_meta($id, 'whapz_option_vertical_pieces', true) . "',
            'whapz_scaling':'" . get_post_meta($id, 'whapz_option_scaling', true) . "',
            'whapz_desktop_image':'" . get_post_meta($id, 'whapz_desktop_image_puzzle', true) . "',
            'whapz_mobile_image':'" . get_post_meta($id, 'whapz_mobile_image_puzzle', true) . "',
            'whapz_canvas_color':'#" . get_post_meta($id, 'whapz_option_canvas_color', true) . "',
            'whapz_opacity_img':'" . get_post_meta($id, 'whapz_option_opacity_img', true) . "',
            'whapz_control_panel_upload':'" . get_post_meta($id, 'whapz_control_panel_upload', true) . "',
            'whapz_post_id':'" . get_post_meta($id, 'whapz_post_id', true) . "'};
        /* ]]> */
        </script>";

        $html .= '<div id="modal_form">
                    <span id="modal_close">X</span>
                    <div class="content">
                    <h4>'.__('Your time','whapz_puzzle').': <span class="whapz-used-minutes"></span>:<span class="whapz-used-second"></span></h4>
                    <div class="wrapper-inner" style="min-height:100px;width:100%;">
                    '. do_shortcode(''.get_post_meta($id, 'whapz_option_congratulations', true).'').
                    '</div>
                    <p>
                    <a class="whapz_fb_share" target="_blank" href="#">
                    <img src='.plugins_url('images/fb.png', __FILE__).'></a>
                    <a class="whapz_tw_share" target="_blank" href="#">
                    <img src='.plugins_url('images/tw.png', __FILE__).'></a>                    
                    <a class="whapz_ln_share" target="_blank" href="#">
                    <img src='.plugins_url('images/in.png', __FILE__).'></a>
                    </p>
                    </div>
                  </div>
                  <div id="overlay"></div>';

        return $html;

    }

    add_shortcode('game-puzzle', 'whapz_whagamepuzzle_func');
}


/**
 * Add shortcode Option Box
 */
if( ! function_exists('whapz_puzzle_add_box_shortcode' ) ) {

    add_action('add_meta_boxes', 'whapz_puzzle_add_box_shortcode', 10);

    function whapz_puzzle_add_box_shortcode() {
        $screens = array('wha_puzzle');
        add_meta_box('whapz_sectionid_shortcode', 'PUZZLE SHORTCODE:', 'whapz_meta_box_shortcode_callback', $screens, 'advanced', 'high');
    }

    function whapz_meta_box_shortcode_callback($post, $meta) {

        wp_nonce_field(plugin_basename(__FILE__), 'whapz_puzzle_noncename');
        echo '<div class="shortcode">[game-puzzle id="' . $post->ID . '"]</div>';
    }
}


/**
 * Add Default Setting Option Box
 */
if( ! function_exists('whapz_puzzle_add_box_setting' ) ) {

    add_action('add_meta_boxes', 'whapz_puzzle_add_box_setting', 20);

    function whapz_puzzle_add_box_setting() {

        $screens = array('wha_puzzle');
        add_meta_box('whapz_option_setting', 'SETTING PUZZLES', 'whapz_setting_box_callback', $screens, 'advanced', 'low');
    }
    function whapz_setting_box_callback($post, $meta) {

        wp_nonce_field(plugin_basename(__FILE__), 'whapz_puzzle_noncename');


        $whapz_horizontal_pieces = !empty(get_post_meta($post->ID, 'whapz_option_horizontal_pieces', true))?
                                          get_post_meta($post->ID, 'whapz_option_horizontal_pieces', true):5;
        $whapz_vertical_pieces   = !empty(get_post_meta($post->ID, 'whapz_option_vertical_pieces', true))?
                                          get_post_meta($post->ID, 'whapz_option_vertical_pieces', true):5;
        $whapz_congratulations   = !empty(get_post_meta($post->ID, 'whapz_option_congratulations', true))?
                                          get_post_meta($post->ID, 'whapz_option_congratulations', true):'Congratulations...';
        $whapz_canvas_color      = !empty(get_post_meta($post->ID, 'whapz_option_canvas_color', true))?
                                          get_post_meta($post->ID, 'whapz_option_canvas_color', true):'FFFFFF';

        $var_opacity_img = (float) get_post_meta($post->ID, 'whapz_option_opacity_img', true);

        $cpu_checked = !empty(get_post_meta($post->ID, 'whapz_control_panel_upload', true)) ? 'checked' : '';

        if($var_opacity_img !== .0) {
            $whapz_opacity_img = $var_opacity_img;
        }
        else if($var_opacity_img === .0) {
            $whapz_opacity_img = .25;
        }
        else {
            $whapz_opacity_img = .0;
        }

        echo '<div class="wha-puzzle-container-admin">';
        echo '<div class="wha-puzzle-admin">';
        echo '<div class="item-block">';
        echo __('<h3>Pieces Horizontally</h3>', 'whapz_puzzle');
        echo '<select class="select-horizontally" name="whapz_horizontal_pieces">';
        for($i = 1; $i <= 10; $i++) {
            if($i == $whapz_horizontal_pieces) {
                echo '<option selected value="'.$i.'">'.$i.'</option>';
            } else {
                echo '<option value="'.$i.'">'.$i.'</option>';
            }
        }
        echo '</select>';
        echo '</div>';

        echo '<div class="item-block">';
        echo __('<h3>Pieces Vertically</h3>', 'whapz_puzzle');
        echo '<select class="select-vertical" name="whapz_vertical_pieces">';
        for($i = 1; $i <= 10; $i++) {
            if($i == $whapz_vertical_pieces) {
                echo '<option selected value="'.$i.'">'.$i.'</option>';
            } else {
                echo '<option value="'.$i.'">'.$i.'</option>';
            }
        }
        echo '</select>';
        echo '</div>';

        echo '<div class="item-block">';
        echo __('<h3>Canvas color</h3>', 'whapz_puzzle');
        echo '<input class="jscolor" name="whapz_canvas_color" value="'.$whapz_canvas_color.'">';
        echo '<input type="hidden" name="post_id" id="post_id" value="'.$post->ID.'" />';
        echo '</div>';

        echo '<div class="item-block">';
        echo __('<h3>Opacity image</h3>', 'whapz_puzzle');
        echo '<div id="whapz-slider">
              <div id="whapz-handle" class="ui-slider-handle">
              <input class="whapz_opacity_img" type="hidden" name="whapz_opacity_img" value="'.(float) $whapz_opacity_img.'">
              <span class="value-wrapper"><span class="value-output"></span>%</span></div></div></div>';
        echo '<div class="item-block last-item">';
        echo __('<h3>Allow image upload on frontend</h3>', 'whapz_puzzle');
        echo '<input type="checkbox" class="whapz-display-panel_upload" name="whapz_control_panel_upload" value="1" '.$cpu_checked.'>';
        echo __('<small>Allow users to upload images on their own. The image on frontend overwrites image inside admin panel.</small>', 'whapz_puzzle');
        echo '</div>';
        echo "<script>
            /* <![CDATA[ */    
            
             var admin_optional_puzzle_vars = {'whapz_admin_opacity_img':'" . (float) $whapz_opacity_img*100 . "'}; 
             
            jQuery(document).ready(function($) {                
                      
            var whapz_value_opacity_img = admin_optional_puzzle_vars.whapz_admin_opacity_img;
            var handle    =  $('.wha-puzzle-container-admin #whapz-handle .value-output');
            var input     =  $('.wha-puzzle-container-admin #whapz-handle .whapz_opacity_img');
            var indicator =  $('.wha-puzzle-container-admin .whapz-indicator-opacity');

            $('#whapz-slider').slider({
                min: .01,
                max: 100.01,
                animate: true,
                value: whapz_value_opacity_img,
                create: function() {
                    handle.text($(this).slider('value'));
                },
                slide: function(event, ui) {
                    handle.text(ui.value);
                    input.val(ui.value/100);
                    indicator.text(ui.value);
                }
                }); 
            });             
            /* ]]> */
            </script>";
        echo '</div>';


        echo '<div class="editor_wrap"><h3>'.__('Congratulations text', 'whapz_puzzle').'</h3>';
        wp_editor($whapz_congratulations, 'whapz_congratulations', $settings = array(
            'wpautop' => true,
            'tinymce' => true,
            'textarea_rows' => 7,
            'textarea_name' => 'whapz_congratulations_message',
        ));
        echo '</div>';
        echo '</div>';
    }

}


/**
 * Save data
 */
if( ! function_exists('whapz_save_data' ) ) {

    add_action('save_post', 'whapz_save_data', 10, 3);

    function whapz_save_data($post_id, $post, $update) {

        if( 'wha_puzzle' === get_post_type($post_id) && $update === true && !in_array($post->post_status, ['trash','untrash']) )
        {

            if (!empty($_POST['whapz_puzzle_noncename']) && !wp_verify_nonce($_POST['whapz_puzzle_noncename'], plugin_basename(__FILE__)))
                return;

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return;

            if (!current_user_can('edit_post', $post_id))
                return;


            if( !isset($_POST['whapz_control_panel_upload']) ) {

                $attachment_id = get_post_thumbnail_id( $post_id );

                $array = wp_get_attachment_metadata( $attachment_id );

                if(!empty($array) && !empty($array['sizes']['thumbnail']['mime-type']) && (
                        $array['sizes']['thumbnail']['mime-type'] == 'image/png' ||
                        $array['sizes']['thumbnail']['mime-type'] == 'image/jpg' ||
                        $array['sizes']['thumbnail']['mime-type'] == 'image/gif' ||
                        $array['sizes']['thumbnail']['mime-type'] == 'image/jpeg'))
                {

                    $url_image  = $array['file'];

                    if(!empty($array['sizes']['desktop-puzzle-image']['file'])) {

                        // for desktop
                        $name_image = $array['sizes']['desktop-puzzle-image']['file'];
                        $meta_arr = explode('/', $url_image );
                        $full_path_img = $meta_arr[0] . '/' . $meta_arr[1] . '/' . $name_image;
                        update_post_meta($post_id, 'whapz_desktop_image_puzzle', $full_path_img);

                    }

                    if(!empty($array['sizes']['mobile-puzzle-image']['file'])) {

                        // for mobile
                        $mobile_name_image = $array['sizes']['mobile-puzzle-image']['file'];
                        $mobile_meta_arr = explode('/', $url_image );
                        $mobile_full_path_img = $mobile_meta_arr[0] . '/' . $mobile_meta_arr[1] . '/' . $mobile_name_image;
                        update_post_meta($post_id, 'whapz_mobile_image_puzzle',  $mobile_full_path_img);
                    }

                    if(empty($array['sizes']['desktop-puzzle-image']['file'])) {

                        $whapz_image_url = get_post_meta($attachment_id, '_wp_attached_file', true);
                        update_post_meta($post_id, 'whapz_desktop_image_puzzle', $whapz_image_url);
                    }

                    if(empty($array['sizes']['mobile-puzzle-image']['file'])) {

                        $whapz_image_url = get_post_meta($attachment_id, '_wp_attached_file', true);
                        update_post_meta($post_id, 'whapz_mobile_image_puzzle',  $whapz_image_url);
                    }
                }
                else {
                    wp_die(__('Select Featured image or Allow image upload on frontend correct format (png, jpg, gif, jpeg)','whapz_puzzle').' 
                    <a href="/wp-admin/post.php?post='.$post_id.'&action=edit">Back edit page</a>');
                }

            }
            else {

                $desktop_image_puzzle = get_post_meta($post_id, 'whapz_desktop_image_puzzle', true);
                $mobile_image_puzzle  = get_post_meta($post_id, 'whapz_mobile_image_puzzle', true);

                if(empty($desktop_image_puzzle)) {
                    update_post_meta($post_id, 'whapz_desktop_image_puzzle', '/wp-content/plugins/wha-puzzle/images/desktop.jpg');
                }

                if(empty($mobile_image_puzzle)) {
                    update_post_meta($post_id, 'whapz_mobile_image_puzzle',  '/wp-content/plugins/wha-puzzle/images/mobile.jpg');
                }

            }


            $whapz_horizontal_pieces = @is_numeric($_POST['whapz_horizontal_pieces'])?$_POST['whapz_horizontal_pieces']:5;
            $whapz_vertical_pieces   = @is_numeric($_POST['whapz_vertical_pieces'])?$_POST['whapz_vertical_pieces']:5;
            $whapz_scaling           = @is_numeric($_POST['post_id'])?'puzzleID-'.$_POST['post_id'].'':0;
            $whapz_congratulations   = @$_POST['whapz_congratulations_message'];
            $whapz_canvas_color      = @(sanitize_hex_color_no_hash($_POST['whapz_canvas_color'])!= null)?$_POST['whapz_canvas_color']:'FFFFFF';
            $whapz_opacity_img  = @(is_numeric($_POST['whapz_opacity_img']) && $_POST['whapz_opacity_img'] >= 0 && $_POST['whapz_opacity_img'] <= 1)?$_POST['whapz_opacity_img']:1;
            $whapz_control_panel_upload = isset($_POST['whapz_control_panel_upload']) ? $_POST['whapz_control_panel_upload'] : 0;
            $whapz_post_id = $post_id;

            update_post_meta($post_id, 'whapz_option_horizontal_pieces', $whapz_horizontal_pieces);
            update_post_meta($post_id, 'whapz_option_vertical_pieces', $whapz_vertical_pieces);
            update_post_meta($post_id, 'whapz_option_scaling', $whapz_scaling);
            update_post_meta($post_id, 'whapz_option_congratulations', $whapz_congratulations);
            update_post_meta($post_id, 'whapz_option_canvas_color', $whapz_canvas_color);
            update_post_meta($post_id, 'whapz_option_opacity_img', $whapz_opacity_img);
            update_post_meta($post_id, 'whapz_control_panel_upload', $whapz_control_panel_upload);
            update_post_meta($post_id, 'whapz_post_id', $whapz_post_id);

        }
    }
}


/**
 * Translit name uploaded file
 */
if( ! function_exists('whapz_translit' ) ) {

    function whapz_translit($text)  {

        $text = mb_strtolower( $text, 'UTF-8' );
        $symbol_table = array('а' => 'a', 'б' => 'b', 'в' => 'v',
            'г' => 'g',	'д' => 'd',	'е' => 'e',
            'ё' => 'yo','ж' => 'zh','з' => 'z',
            'и' => 'i',	'й' => 'j',	'к' => 'k',
            'л' => 'l',	'м' => 'm',	'н' => 'n',
            'о' => 'o',	'п' => 'p',	'р' => 'r',
            'с' => 's',	'т' => 't',	'у' => 'u',
            'ф' => 'f',	'х' => 'h',	'ц' => 'c',
            'ч' => 'ch',	'ш' => 'sh','щ' => 'shh',
            'ъ' => "",	'ы' => 'y',	'ь' => "",
            'э' => 'e',	'ю' => 'yu',	'я' => 'ya');
        $text = strtr( $text, $symbol_table );
        return $text;
    }

}


/**
 * AJAX Upload Image
 */
function whapz_add_frontend_image() {

    $response = array();

    $post_id = $_POST['id_post'];

    $attachment_id = media_handle_upload( 'imagefile', $post_id );

    if ( is_wp_error( $attachment_id ) ) {

        $response['status'] = 0;
    }
    else {

        $array = wp_get_attachment_metadata( $attachment_id );

        $url_image  = $array['file'];

        if( !empty($array['sizes']['desktop-puzzle-image']['file']) ) {

            // for desktop
            $name_image = $array['sizes']['desktop-puzzle-image']['file'];
            $meta_arr = explode('/', $url_image );
            $full_path_img = $meta_arr[0] . '/' . $meta_arr[1] . '/' . $name_image;
            update_post_meta($post_id, 'whapz_desktop_image_puzzle', $full_path_img);

        }

        if( !empty($array['sizes']['mobile-puzzle-image']['file']) ) {

            // for mobile
            $mobile_name_image = $array['sizes']['mobile-puzzle-image']['file'];
            $mobile_meta_arr = explode('/', $url_image );
            $mobile_full_path_img = $mobile_meta_arr[0] . '/' . $mobile_meta_arr[1] . '/' . $mobile_name_image;
            update_post_meta($post_id, 'whapz_mobile_image_puzzle',  $mobile_full_path_img);
        }

        if( empty($array['sizes']['desktop-puzzle-image']['file']) ) {

            $whapz_image_url = get_post_meta($attachment_id, '_wp_attached_file', true);
            update_post_meta($post_id, 'whapz_desktop_image_puzzle', $whapz_image_url);
        }

        if( empty($array['sizes']['mobile-puzzle-image']['file']) ) {

            $whapz_image_url = get_post_meta($attachment_id, '_wp_attached_file', true);
            update_post_meta($post_id, 'whapz_mobile_image_puzzle',  $whapz_image_url);
        }

        $response['status'] = 1;
    }

    echo json_encode($response);

    wp_die();
}

add_action( 'wp_ajax_frontend_image', 'whapz_add_frontend_image' );
add_action( 'wp_ajax_nopriv_frontend_image', 'whapz_add_frontend_image' );


/**
 * Call Areachart option fields and save
 */
function whapz_sidebar_meta_box() {
    add_meta_box(
        'whapz_sidebar',
        __('&nbsp;', 'whapz_puzzle'),
        'whapz_sidebar_meta_box_callback',
        'wha_puzzle',
        'side'
    );
}

add_action('add_meta_boxes', 'whapz_sidebar_meta_box', 2);

function whapz_sidebar_meta_box_callback($post, $meta) {
    $item = '';
    $item .= '<h1>Plugin Developed by</h1>';
    $item .= '<div class="whacs_logo_wrap"><img src="' . plugins_url("images/wha-logo.svg", __FILE__) . '" width="10px" alt="wha_logo"></div>';
    $item .= '<h2><wha>WHA</wha> is team of  top-notch WordPress developers.</h2>';
    $item .= '<h4>Our advantages:</h4>';
    $item .= '
              <ul class="whacs_sidebar_list">
                <li><wha>—</wha> TOP 20 WordPress companies on Clutch;</li>
                <li><wha>—</wha> More than 4 years of experience;</li>
                <li><wha>—</wha> NDA for each project;</li>
                <li><wha>—</wha> Dedicate project manager for each project;</li>
                <li><wha>—</wha> Flexible working hours;</li>
                <li><wha>—</wha> Friendly management;</li>
                <li><wha>—</wha> Clear workflow;</li>
                <li><wha>—</wha> Based in Europe, you can easily reach us via any airlines;</li>
            </ul>';

    $item .= '<h3>Looking for dedicated team?</h3>';

    $item .= '  <a href="https://webhelpagency.com/say-hello/?title=wporg_free_consultation" class="btn btn-reverse btn-arrow">
                <span>Start a Project<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 36.1 25.8" enable-background="new 0 0 36.1 25.8" xml:space="preserve"><g><line fill="none" stroke="#FFFFFF" stroke-width="3" stroke-miterlimit="10" x1="0" y1="12.9" x2="34" y2="12.9"></line><polyline fill="none" stroke="#FFFFFF" stroke-width="3" stroke-miterlimit="10" points="22.2,1.1 34,12.9 22.2,24.7   "></polyline></g></svg>
                </span></a>';

    echo $item;
}
