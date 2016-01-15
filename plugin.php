<?php
/* Plugin Name: WPML Link Manager
 * Description: Makes Link Manager (in the core before WP 3.5) compatible with WPML > 3.2
 * Author: OnTheGoSystems
 * Version: 0.1-dev
 */

function wpml_link_manager_load_plugin() {
    global $pagenow;
    new WPML_Link_Manager( $pagenow );
}
add_action( 'wpml_after_setup', 'wpml_link_manager_load_plugin' );
