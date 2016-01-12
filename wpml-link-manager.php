<?php
/* Plugin Name: WPML Link Manager
 * Description: Makes Link Manager (in the core before WP 3.5) compatible with WPML > 3.2
 * Author: Pierre S.
 * Version: 0.1-beta
 */

Class WPML_Link_Manager {


	private $package_type = 'Link Manager';


	public function __construct() {

		// Continue only if Link Manager is active
		if ( !apply_filters( 'pre_option_link_manager_enabled', false ) )
			return false;

		register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );

		$this->hooks();
	}


	/**
	 * Hook the methods
	 */
	public function hooks() {

		// Attach hooks
		add_action( 'add_link',                      array( $this, 'add_or_edit_link_action' ) );
		add_action( 'edit_link',                     array( $this, 'add_or_edit_link_action' ) );
		add_filter( 'get_bookmarks',                 array( $this, 'get_bookmarks_filter' ) );
		add_action( 'deleted_link',                  array( $this, 'deleted_link_action' ) );
		add_action( 'add_meta_boxes',                array( $this, 'add_meta_boxes_action' ) );
		add_action( 'wpml_register_string_packages', array( $this, 'wpml_register_string_packages_action' ) );
	}


	/**
	 * "add_link" or "edit_link" action
	 *
	 * @ int $link_id
	 */
	public function add_or_edit_link_action( $link_id ) {
		$this->add_strings_package( $link_id );
		$this->synchronize_link_categories( $link_id );
	}


	/**
	 * Register the strings from link object as a package
	 * This is only for the "link_name" and "link_description"
	 *
	 * @param int $link_id
	 */
	private function add_strings_package( $link_id ) {

		$link = get_bookmark( $link_id );

		$package = $this->get_package( $link );
		$name_string_name = $this->get_string_name( 'name', $link );
		$description_string_name = $this->get_string_name( 'description', $link );

		do_action( 'wpml_register_string', $link->link_name, $name_string_name, $package, 'Link title', 'LINE');
		do_action( 'wpml_register_string', $link->link_description, $description_string_name, $package, 'Link description', 'AREA');
	}


	/**
	 * Translate the strings of the link object
	 * Filter only on front-end
	 *
	 * @param array of $link objects from get_bookmark()
	 *
	 * @return array of $link objects
	 */
	public function get_bookmarks_filter( $links ) {

		if ( is_admin() )
			return $links;

		foreach ( $links as $link ) {

			$package = $this->get_package( $link );
			$name_string_name = $this->get_string_name( 'name', $link );
			$description_string_name = $this->get_string_name( 'description', $link );

			$link->link_name = apply_filters( 'wpml_translate_string', $link->link_name, $name_string_name, $package );
			$link->link_description = apply_filters( 'wpml_translate_string', $link->link_description, $description_string_name, $package );
		}

		return $links;
	}


	/**
	 * "deleted_link" action
	 *
	 * @param int $link_id
	 */
	public function deleted_link_action( $link_id ) {
		$this->delete_strings_package( $link_id );
	}


	/**
	 * Remove the string package when the link is deleted
	 *
	 * @param int $link_id
	 */
	private function delete_strings_package( $link_id ) {
		do_action( 'wpml_delete_package_action', $link_id, 'Link Manager' );
	}


	/**
	 * Add a metabox to the link edit form
	 */
	public function add_meta_boxes_action() {
		add_meta_box( 'link-translation', __( 'Link translation', 'wpml-link-manager' ), array( $this, 'render_package_language_ui' ), 'link', 'side', 'default' );
	}


	/**
	 * Render the WPML Package translation UI
	 */
	public function render_package_language_ui() {
		$link_id = isset( $_GET['link_id'] ) ? $_GET['link_id'] : false;

		$package = $this->get_package( $link_id );

		do_action( 'wpml_show_package_language_ui', $package );
	}


	/**
	 * Returns a string package
	 *
	 * @param mixed int|object $link
	 *
	 * @return array $package
	 */
	private function get_package( $link ) {

		/**
		 * More data if object
		 * This is important when we are registering
		 * the package in the database
		 */
		if ( is_object( $link ) ) {
			$package = array(
				'kind' 		=> $this->package_type,
				'name' 		=> $link->link_id,
				'title' 	=> $link->link_name,
				'edit_link' => admin_url( 'link.php?action=edit&link_id=' . $link->link_id ),
				'view_link' => $link->link_url,
			);
		} else {
			$package = array(
				'kind' 		=> $this->package_type,
				'name' 		=> $link,
			);
		}

		return $package;
	}


	/**
	 * Format the string name including the link ID
	 *
	 * @param string $name
	 * @param object $link
	 *
	 * @return string formatted
	 */
	private function get_string_name( $name, $link ) {
		return 'link-' . $link->link_id . '-' . $name;
	}


	/**
	 * Perform some action when plugin is activated
	 */
	public function plugin_activation() {
		// Force package refresh
		update_option( 'wpml-package-translation-refresh-required', true );
	}


	/**
	 * If some links already exists before activation
	 * we will create the missing packages
	 *
	 * @param string $type => empty string...
	 * @param array $existing => NULL... have to verify the hook
	 */
	public function wpml_register_string_packages_action( $type, $existing ) {

		if ( !empty( $type ) && $type != $this->package_type )
			return false;

		$links = get_bookmarks();

		if ( !$links )
			return false;

		foreach ( $links as $link ) {
			$this->add_strings_package( $link->link_id );
		}

		add_action( 'admin_notices', array( $this, 'links_updated_notice') );
	}


	/**
	 * Add an update message after plugin activation
	 * if there was already links in the Link Manager
	 */
	public function links_updated_notice() {
	?>
		<div class="updated">
		<p><?php _e( 'Previous existing links are now availables for translation', 'wpml-link-manager' ); ?></p>
		</div>
	<?php
	}


	/**
	 * Synchronize link categories on save
	 * Assign the translated terms to the same link object
	 * => It's more like a term duplication because they will
	 * all point to the same object, but it should be enough
	 * for links
	 *
	 * @param int $link_id
	 */
	private function synchronize_link_categories( $link_id ) {

		$cats = $new_cats = wp_get_link_cats( $link_id );
		$langs = apply_filters( 'wpml_active_languages', array() );

		foreach ( $langs as $lang ) {

			foreach ( $cats as $cat ) {

				$new_cats[] = apply_filters( 'wpml_object_id', $cat, 'link_category', true, $lang['language_code'] );

			}

		}

		$new_cats = array_map( 'intval', $new_cats );
		$new_cats = array_unique( $new_cats );

		remove_action('set_object_terms', array( 'WPML_Terms_Translations', 'set_object_terms_action' ), 10 );
		wp_set_link_cats( $link_id, $new_cats );
		add_action( 'set_object_terms', array( 'WPML_Terms_Translations', 'set_object_terms_action' ), 10, 6 );
	}
}

new WPML_Link_Manager();