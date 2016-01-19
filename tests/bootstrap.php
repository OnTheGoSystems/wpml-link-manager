<?php
if ( ! defined( 'WPML_CORE_PATH' ) ) {
	define( 'WPML_CORE_PATH', dirname( __FILE__ ) . '/../../sitepress-multilingual-cms' );
}
if ( ! defined( 'WPML_CORE_ST_PATH' ) ) {
	define( 'WPML_CORE_ST_PATH', dirname( __FILE__ ) . '/../../wpml-string-translation' );
}
if ( ! defined( 'WPML_CORE_TM_PATH' ) ) {
	define( 'WPML_CORE_TM_PATH', dirname( __FILE__ ) . '/../../wpml-translation-management' );
}
define( 'WPML_ST_TEST_DIR', dirname( __FILE__ ) . '/../../wpml-string-translation/tests' );
$_tests_dir = isset( $_ENV['WP_TEST_DIR'] ) ? $_ENV['WP_TEST_DIR'] : 'wordpress-tests-lib';
require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require WPML_CORE_PATH . '/tests/util/functions.php';
	require WPML_CORE_PATH . '/sitepress.php';
	require WPML_CORE_ST_PATH . '/plugin.php';
	require WPML_CORE_TM_PATH . '/plugin.php';
	require dirname( __FILE__ ) . '/../plugin.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

function _make_wpml_setup_complete() {
	icl_set_setting( 'setup_complete', 1, true );
}
tests_add_filter( 'wpml_loaded', '_make_wpml_setup_complete' );

// Make sure Link Manager plugin is on
function _enable_link_manager() {
	return true;
}
tests_add_filter( 'pre_option_link_manager_enabled', '_enable_link_manager', 1000 );

require $_tests_dir . '/includes/bootstrap.php';
require WPML_CORE_PATH . '/tests/util/wpml-unittestcase.class.php';
//require WPML_CORE_ST_PATH . '/tests/util/wpml-st-unittestcase.class.php';