<?php

class Test_WPML_Link_Manager_Helper extends WPML_UnitTestCase {

	private $lm_helper;

	public function setUp() {
		parent::setUp();
		$package_type = 'Link Manager';
		$this->lm_helper = new WPML_Link_Manager_Helper( $package_type );
	}

	public function test_get_package_type() {
		$package_type = 'Link Manager';
		$this->lm_helper = new WPML_Link_Manager_Helper( $package_type );

		$this->assertEquals( $this->lm_helper->get_package_type(), $package_type);

	}

	public function test_get_package() {
		$link = new stdClass();
		$link->link_id   = 3;
		$link->link_name = 'test link';
		$link->link_url  = 'http://example.com';
		$package = $this->lm_helper->get_package( $link );
		$this->assertEquals( 'link', substr( $package['kind'], -4, 4 ) );

		$cat = new stdClass();
		$cat->term_id = 6;
		$cat->name    = 'test cat';
		$package = $this->lm_helper->get_package( $cat, 'category' );
		$this->assertEquals( 'category', substr( $package['kind'], -8, 8 ) );

		$package = $this->lm_helper->get_package( 19 );
		$this->assertEquals( 'link', substr( $package['kind'], -4, 4 ) );

		$package = $this->lm_helper->get_package( 19, 'category' );
		$this->assertEquals( 'category', substr( $package['kind'], -8, 8 ) );
	}

	public function test_get_link_string_name() {
		$name = 'name';
		$link = new stdClass();
		$link->link_id = 12;

		$str_name = $this->lm_helper->get_link_string_name( $name, $link );
		$this->assertEquals( 'link-' . $link->link_id . '-' . $name, $str_name );
	}

	public function test_get_category_string_name() {
		$name = 'name';
		$cat = new stdClass();
		$cat->term_id = 15;

		$str_name = $this->lm_helper->get_category_string_name( $name, $cat );
		$this->assertEquals( 'link-category-' . $cat->term_id . '-' . $name, $str_name );
	}
}