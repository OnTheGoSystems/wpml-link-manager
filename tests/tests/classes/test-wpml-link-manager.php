<?php

//require_once( WPML_CORE_ST_PATH . '/tests/util/wpml-st-unittestcase.class.php' );
require_once( WPML_LINK_MANAGER_PATH . '/classes/class-wpml-link-manager.php' );
require_once( WPML_LINK_MANAGER_PATH . '/classes/class-wpml-link-manager-helper.php' );

class Test_WPML_Link_Manager extends WPML_UnitTestCase {

    public function test_add_strings_package() {
        global $wpdb;

        $args = array(
                "link_url"		=> 'http://test.com',
                "link_name"		=> 'The link name',
                "link_description"	=> 'The link description',
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

        $this->assertTrue( (bool) $name_string_id );
        $this->assertTrue( (bool) $desc_string_id );

    }


    public function test_get_bookmarks_filter() {
        $orig_lang = 'en';
        $sec_lang  = 'fr';
        $context   = 'Context : ' . rand_str();
        $salt      = rand_str( 6 );
        $name_base = 'The link ';
        $desc_base = 'The link description ';
        $pagenow   = 'index.php';

        $wpml_link_manager = new WPML_Link_Manager( $pagenow );

        // Create 3 links and add translations
        $links = array();
        for ($i=0; $i < 3; $i++) {
            $args = array(
                    "link_url" => 'http://test' . $i,
                    "link_name" => $name_base . $i,
                    "link_description" => $desc_base . $i,
            );

            $link_id = wp_insert_link( $args );

            $links[ $i ] = get_bookmark( $link_id );

            $name_st_id = icl_register_string( $context, 'Name' . $i . $salt, $name_base . $i, false, $orig_lang );
            $desc_st_id = icl_register_string( $context, 'Desc' . $i . $salt, $name_base . $i, false, $orig_lang );

            icl_add_string_translation( $name_st_id, $sec_lang, $name_base . $i . $sec_lang, ICL_TM_COMPLETE );
            icl_add_string_translation( $desc_st_id, $sec_lang, $name_base . $i . $sec_lang, ICL_TM_COMPLETE );
        }

        $translated_links = $wpml_link_manager->get_bookmarks_filter( $links );

        foreach ( $translated_links as $i => $link ) {
            $this->assertEquals( $name_base . $i . $sec_lang, $link->link_name );
            $this->assertEquals( $desc_base . $i . $sec_lang, $link->link_description );
        }

    }
}