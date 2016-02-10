<?php
/* Plugin Name: WPML Link Manager
 * Description: Makes Link Manager (in the core before WP 3.5) compatible with WPML > 3.2
 * Author: OnTheGoSystems
 * Author URL: http://wpml.org/
 * Version: 0.1-dev
 */

define( 'WPML_LINK_MANAGER_PATH', dirname( __FILE__ ) );

function wpml_link_manager_load_plugin() {
    global $pagenow;

    $wpml_auto_loader_instance = WPML_Auto_Loader::get_instance();
    $wpml_auto_loader_instance->register( WPML_LINK_MANAGER_PATH . '/' );

    $package_type = 'Link Manager';
    $helper = new WPML_Link_Manager_Helper( $package_type );
    new WPML_Link_Manager( $pagenow, $helper );
}
add_action( 'wpml_loaded', 'wpml_link_manager_load_plugin' );

function wpml_link_manager_maybe_remove_admin_ls() {
    global $pagenow;
    if ( $pagenow === 'link.php'
        || $pagenow === 'link-manager.php'
        || $pagenow === 'link-add.php'
        || ( $pagenow === 'edit-tags.php' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'link_category' ) ) {
            add_filter( 'wpml_show_admin_language_switcher', '__return_false' );
    }
}
add_action( 'wpml_before_init', 'wpml_link_manager_maybe_remove_admin_ls' );

function wpml_link_manager_activation() {
    update_option( 'wpml-package-translation-refresh-required', true );
}
register_activation_hook( __FILE__, 'wpml_link_manager_activation' );