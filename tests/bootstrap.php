<?php
if ( ! defined( 'WPML_CORE_PATH' ) ) {
	define( 'WPML_CORE_PATH', dirname( __FILE__ ) . '/../../sitepress-multilingual-cms' );
}
if ( ! defined( 'WPML_CORE_ST_PATH' ) ) {
	define( 'WPML_CORE_ST_PATH', dirname( __FILE__ ) . '/../../wpml-string-translation' );
}
$_tests_dir = isset( $_ENV['WP_TEST_DIR'] ) ? $_ENV['WP_TEST_DIR'] : 'wordpress-tests-lib';
require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require WPML_CORE_PATH . '/tests/util/functions.php';
	require WPML_CORE_PATH . '/sitepress.php';
	require WPML_CORE_ST_PATH . '/plugin.php';
	require dirname( __FILE__ ) . '/../plugin.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
require WPML_CORE_PATH . '/tests/util/wpml-unittestcase.class.php';