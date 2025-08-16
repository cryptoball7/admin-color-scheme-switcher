<?php
/**
 * Plugin Name: Admin Color Scheme Switcher
 * Description: Adds a dropdown in the admin toolbar to quickly switch WP admin color schemes.
 * Version: 1.0
 * Author: Cryptoball cryptoball7@gmail.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add dropdown to admin toolbar
 */
add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
    if ( ! is_user_logged_in() || ! is_admin_bar_showing() ) {
        return;
    }

    $user_id = get_current_user_id();
    $current_scheme = get_user_option( 'admin_color', $user_id );
    global $_wp_admin_css_colors;

    // Parent menu item
    $wp_admin_bar->add_node( array(
        'id'    => 'admin-color-switcher',
        'title' => 'Admin Color',
        'meta'  => array( 'class' => 'admin-color-switcher-parent' )
    ) );

    // Child items for each color scheme
    foreach ( $_wp_admin_css_colors as $scheme => $info ) {
        $title = sprintf(
            '<span style="display:inline-block;width:12px;height:12px;background:%s;margin-right:5px;border:1px solid #ccc;"></span> %s',
            esc_attr( $info->colors[0] ),
            esc_html( $info->name )
        );

        $wp_admin_bar->add_node( array(
            'id'     => 'admin-color-' . $scheme,
            'parent' => 'admin-color-switcher',
            'title'  => $title,
            'href'   => wp_nonce_url(
                add_query_arg( array(
                    'acs_switch' => $scheme
                ), admin_url() ),
                'acs_switch_color'
            )
        ) );
    }
}, 100 );

/**
 * Handle the scheme change request
 */
add_action( 'admin_init', function() {
    if ( ! is_user_logged_in() || ! isset( $_GET['acs_switch'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'acs_switch_color' ) ) {
        wp_die( 'Security check failed' );
    }

    $user_id = get_current_user_id();
    $new_scheme = sanitize_key( $_GET['acs_switch'] );

    global $_wp_admin_css_colors;
    if ( array_key_exists( $new_scheme, $_wp_admin_css_colors ) ) {
        update_user_meta( $user_id, 'admin_color', $new_scheme );
    }

    // Redirect to remove query params
    wp_safe_redirect( remove_query_arg( array( 'acs_switch', '_wpnonce' ) ) );
    exit;
});
