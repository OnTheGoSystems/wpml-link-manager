<?php

//require_once( WPML_CORE_ST_PATH . '/tests/util/wpml-st-unittestcase.class.php' );
require_once( WPML_LINK_MANAGER_PATH . '/classes/wpml-link-manager.php' );

class Test_WPML_Link_Manager extends WPML_UnitTestCase {

    public function test_add_strings_package() {
        global $wpdb;

        $args = array(
	        'link_url'         => 'http://test.com',
	        'link_name'        => 'The link name',
	        'link_description' => 'The link description',
        );
        $link_id = wp_insert_link( $args );
        $pagenow = 'link.php';
        $wpml_link_manager = new WPML_Link_Manager( $pagenow );
        $wpml_st_string_factory = new WPML_ST_String_Factory( $wpdb );

        $wpml_link_manager->add_or_edit_link_action( $link_id );

        // check if name & description strings are registered
        $context = 'link-manager-link-' . $link_id;
        $name_name = 'link-' . $link_id. '-name';
        $name_desc = 'link-' . $link_id. '-description';

        $name_string_id = $wpml_st_string_factory->get_string_id( $args['link_name'], $context,  $name_name );
        $desc_string_id = $wpml_st_string_factory->get_string_id( $args['link_description'], $context, $name_desc );

        $this->assertTrue((bool) $name_string_id );
        $this->assertTrue((bool) $desc_string_id );

    }
}