<?php
/* Plugin Name: WPML Link Manager
 * Description: Makes Link Manager (in the core before WP 3.5) compatible with WPML > 3.2
 * Author: OnTheGoSystems
 * Version: 0.1-dev
 */

define( 'WPML_LINK_MANAGER_PATH', dirname( __FILE__ ) );
require_once( WPML_LINK_MANAGER_PATH . '/classes/wpml-link-manager.php' );

function wpml_link_manager_load_plugin() {
    global $pagenow;
    new WPML_Link_Manager( $pagenow );
}
add_action( 'wpml_loaded', 'wpml_link_manager_load_plugin' );


/**
 * Remove admin language switcher in some pages
 * todo: Include this function in WPML_Link_Manager class when "wpml_show_admin_language_switcher" will be later
 * => Currently this filter is fired in "plugins_loaded" (priority 1)
 */
function maybe_remove_admin_language_switcher() {
    global $pagenow;
    if ( $pagenow === 'link.php'
        || $pagenow === 'link-manager.php'
        || ( $pagenow === 'edit-tags.php' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'link_category' ) ) {
        add_filter( 'wpml_show_admin_language_switcher', '__return_false' );
    }
}
add_action( 'wpml_show_admin_language_switcher', 'maybe_remove_admin_language_switcher' );