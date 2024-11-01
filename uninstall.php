<?php
/**
 * Uninstall plugin WHA puzzle
 * Trigger Uninstall process only if WP_UNINSTALL_PLUGIN is defined
 */

if(!defined('WP_UNINSTALL_PLUGIN')) exit;

    global $wpdb;

    // Delete data from table wp_postmeta
    $wpdb->get_results('DELETE FROM wp_postmeta WHERE meta_key IN (
                                  "whapz_desktop_image_puzzle", 
                                  "whapz_mobile_image_puzzle", 
                                  "whapz_option_canvas_color", 
                                  "whapz_option_horizontal_pieces", 
                                  "whapz_option_vertical_pieces",
                                  "whapz_option_opacity_img",
                                  "whapz_option_scaling",
                                  "whapz_option_congratulations")');


    // Delete data from table wp_posts
    $wpdb->get_results('DELETE FROM wp_posts WHERE post_type IN ("wha_puzzle")');









