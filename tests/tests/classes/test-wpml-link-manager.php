<?php

//require_once( WPML_LINK_MANAGER_PATH . '/classes/class-wpml-link-manager.php' );
//require_once( WPML_LINK_MANAGER_PATH . '/classes/class-wpml-link-manager-helper.php' );
//require_once( WPML_CORE_ST_PATH . '/inc/package-translation/inc/wpml-package-translation-helper.class.php' );

class Test_WPML_Link_Manager extends WPML_UnitTestCase {

    // Set by $this->instantiate_link_manager();
    protected $lm;
    protected $lm_helper;

    public function test_add_strings_package() {
        $this->instantiate_link_manager( 'link.php' );

        $args = array(
                "link_url"		=> 'http://test.com',
                "link_name"		=> 'The link name',
                "link_description"	=> 'The link description',
        );
        $link_id = wp_insert_link( $args );

        $link_has_strings = $this->link_has_strings( $link_id );

        $this->assertTrue( $link_has_strings );

    }

    public function test_get_bookmarks_filter() {
        global $sitepress;
        $this->instantiate_link_manager( 'link.php' ); // To allow registering strings

        $orig_lang    = 'en';
        $sec_lang     = 'fr';
        $name_base    = 'The link ';
        $desc_base    = 'The link description ';

        $sitepress->switch_lang( $orig_lang );

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
            $this->lm->add_or_edit_link_action( $links[ $i ] );

            $context = $this->get_link_string_context( $link_id );

            // Add translations
            $name_st_id = icl_get_string_id( $links[ $i ]->link_name, $context, $this->lm_helper->get_link_string_name( 'name', $links[ $i ] ) );
            $desc_st_id = icl_get_string_id( $links[ $i ]->link_description, $context, $this->lm_helper->get_link_string_name( 'description', $links[ $i ] ) );

            icl_add_string_translation( $name_st_id, $sec_lang, $name_base . $i . $sec_lang, ICL_TM_COMPLETE );
            icl_add_string_translation( $desc_st_id, $sec_lang, $desc_base . $i . $sec_lang, ICL_TM_COMPLETE );
        }

        $sitepress->switch_lang( $sec_lang );
        $this->instantiate_link_manager( 'front' ); // Switch to front end

        $translated_links = $this->lm->get_bookmarks_filter( $links );

        foreach ( $translated_links as $i => $link ) {
            $this->assertEquals( $name_base . $i . $sec_lang, $link->link_name );
            $this->assertEquals( $desc_base . $i . $sec_lang, $link->link_description );
        }

    }

    public function test_deleted_link_action() {
        $this->instantiate_link_manager( 'link.php' );

        $args = array(
                "link_url"		    => 'http://test.com',
                "link_name"		    => 'The link name',
                "link_description"	=> 'The link description',
        );

        $link_id = wp_insert_link( $args );
        $link    = get_bookmark( $link_id );

        $package = $this->lm_helper->get_package( $link, 'link' );

        $this->assertTrue( $this->package_exist_in_DB( $package ) );

        $this->lm->deleted_link_action( $link_id );

        $this->assertFalse( $this->package_exist_in_DB( $package ) );

    }

    public function test_plugin_activation_action() {
        $this->instantiate_link_manager();
        $this->lm->plugin_activation_action();

        $option = get_option( 'wpml-package-translation-refresh-required' );

        $this->assertTrue( $option );
    }

    public function test_get_terms_filter() {

    }

    public function test_created_or_edited_link_category_action() {

    }

    private function instantiate_link_manager( $pagenow = 'front' ) {

        set_current_screen( $pagenow );

        $this->reload_package_translation(); // depends on current screen

        $package_type    = 'Link Manager';
        $this->lm_helper = new WPML_Link_Manager_Helper( $package_type );
        $this->lm        = new WPML_Link_Manager( $pagenow, $this->lm_helper );

        // Fire again plugins_loaded action
        $this->lm->plugins_loaded_action();
    }

    private function reload_package_translation() {
        global $WPML_package_translation;
        $WPML_package_translation = new WPML_Package_Translation();
        $WPML_package_translation->loaded();
    }

    private function get_link_string_context( $link_id ) {
        $package_helper = new WPML_Package_Helper();
        return $package_helper->get_string_context_from_package( $this->lm_helper->get_package( $link_id, 'link' ) );
    }

    private function link_has_strings( $link_id ) {
        $ret = false;
        // check if name & description strings are registered
        $link = get_bookmark( $link_id );
        $context = $this->get_link_string_context( $link_id );
        $name_name = $this->lm_helper->get_link_string_name( 'name', $link );
        $name_desc = $this->lm_helper->get_link_string_name( 'description', $link );

        if ( icl_get_string_id( $link->link_name, $context, $name_name ) && icl_get_string_id( $link->link_description, $context, $name_desc ) ) {
            $ret = true;
        }

        return (bool) $ret;
    }

    private function package_exist_in_DB( $package ) {
        global $wpdb;

        $query         = "SELECT ID FROM {$wpdb->prefix}icl_string_packages WHERE kind=%s AND name=%s";
        $query_prepare = $wpdb->prepare( $query, $package['kind'], $package['name'] );
        $ret           = $wpdb->get_var( $query_prepare );

        return (bool) $ret;
    }
}