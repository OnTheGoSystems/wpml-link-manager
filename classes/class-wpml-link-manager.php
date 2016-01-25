<?php

Class WPML_Link_Manager {

	private $pagenow;
	private $helper;

	/**
	 * @param object $pagenow
	 * @param object $helper
	 */
	public function __construct( &$pagenow, &$helper ) {
		$this->pagenow = &$pagenow;
		$this->helper  = &$helper;
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded_action' ) );
	}

	public function plugins_loaded_action() {
		if ( !apply_filters( 'pre_option_link_manager_enabled', false ) ) {
			return;
		}

		$this->hooks();
		$this->maybe_add_package_language_switcher();
	}

	public function hooks() {
		add_action( 'add_link',                      array( $this, 'add_or_edit_link_action' ) );
		add_action( 'edit_link',                     array( $this, 'add_or_edit_link_action' ) );
		add_filter( 'get_bookmarks',                 array( $this, 'get_bookmarks_filter' ) );
		add_action( 'deleted_link',                  array( $this, 'deleted_link_action' ) );
		add_action( 'add_meta_boxes',                array( $this, 'add_meta_boxes_action' ) );
		add_action( 'wpml_register_string_packages', array( $this, 'wpml_register_string_packages_action' ) );
		add_filter( 'get_terms',                     array( $this, 'get_terms_filter' ), 10, 2 );
		add_action( 'created_link_category',         array( $this, 'created_or_edited_link_category_action' ) );
		add_action( 'edited_link_category',          array( $this, 'created_or_edited_link_category_action' ) );
		add_action( 'deleted_link',                  array( $this, 'deleted_link_action' ), 10, 4 );
		add_action( 'delete_term',                   array( $this, 'delete_term_action' ), 10, 3 );
	}

	public function maybe_add_package_language_switcher() {
		if ( $this->pagenow === 'link.php'
			&& isset( $_GET['action'], $_GET['link_id'] )
			&& $_GET['action'] === 'edit'
		) {
				$link_id = filter_input(INPUT_GET, 'link_id');
				$package = $this->helper->get_package($link_id);
				do_action('wpml_show_package_language_admin_bar', $package);
		} else if ( $this->pagenow === 'edit-tags.php'
					&& isset( $_GET['taxonomy'], $_GET['tag_ID'] )
					&& $_GET['taxonomy'] === 'link_category'
		) {
				$tag_id = filter_input( INPUT_GET, 'tag_ID' );
				$package = $this->helper->get_package( $tag_id, 'category' );
				do_action( 'wpml_show_package_language_admin_bar', $package );
		}
	}

	/**
	 * @param int $link_id
	 */
	public function add_or_edit_link_action( $link_id ) {
		$this->add_strings_package( $link_id );
	}

	/**
	 * @param int $link_id
	 */
	private function add_strings_package( $link_id ) {
		$link = get_bookmark( $link_id );

		$package = $this->helper->get_package( $link );
		$name_string_name = $this->helper->get_link_string_name( 'name', $link );
		$description_string_name = $this->helper->get_link_string_name( 'description', $link );

		do_action( 'wpml_register_string', $link->link_name, $name_string_name, $package, 'Link title', 'LINE');
		do_action( 'wpml_register_string', $link->link_description, $description_string_name, $package, 'Link description', 'AREA');
	}

	/**
	 * @param array $links objects from get_bookmark()
	 *
	 * @return array
	 */
	public function get_bookmarks_filter( $links ) {
		if ( !is_admin() ) {
			foreach ( $links as &$link ) {
				$package = $this->helper->get_package($link);
				$name_string_name = $this->helper->get_link_string_name('name', $link);
				$description_string_name = $this->helper->get_link_string_name('description', $link);

				$link->link_name = apply_filters('wpml_translate_string', $link->link_name, $name_string_name, $package);
				$link->link_description = apply_filters('wpml_translate_string', $link->link_description, $description_string_name, $package);
			}
		}
		return $links;
	}

	/**
	 * @param int $link_id
	 */
	public function deleted_link_action( $link_id ) {
		$this->delete_strings_package( $link_id, 'link' );
	}

	/**
	 * @param int    $link_id
	 * @param string $subtype
	 */
	private function delete_strings_package( $link_id, $subtype ) {
		do_action( 'wpml_delete_package_action', $link_id, $this->helper->get_package_type() . ' - ' . $subtype );
	}

	public function add_meta_boxes_action() {
		add_meta_box( 'link-translation', __( 'Link translation', 'wpml-link-manager' ), array( $this, 'render_package_language_ui' ), 'link', 'side', 'default' );
	}

	public function render_package_language_ui() {
		$link_id = isset( $_GET['link_id'] ) ? $_GET['link_id'] : false;

		$package = $this->helper->get_package( $link_id );

		do_action( 'wpml_show_package_language_ui', $package );
	}

	public function wpml_register_string_packages_action() {

		$links = get_bookmarks();

		if ( $links ) {
			foreach ( $links as $link ) {
				$this->add_strings_package( $link->link_id );
			}
		}

		$link_categories = get_terms( 'link_category' );

		if ( $link_categories ) {
			foreach ( $link_categories as $link_category ) {
				$this->created_or_edited_link_category_action( $link_category->term_id );
			}
		}

		ICL_AdminNotifier::add_instant_message(
			__( 'Previous existing links are now available for translation', 'wpml-link-manager' ),
			'update'
		);
	}

	/**
	 * @param array $categories
	 * @param array $taxonomies
	 *
	 * @return string $cat_name Category name
	 */
	public function get_terms_filter( $categories, $taxonomies ) {

		if ( !is_admin() && in_array( 'link_category', $taxonomies ) ) {
			foreach ( $categories as &$category ) {
				$package = $this->helper->get_package( $category, 'category' );
				$name_string_name = $this->helper->get_category_string_name( 'name', $category );
				$description_string_name = $this->helper->get_category_string_name( 'description', $category );

				$category->name = apply_filters( 'wpml_translate_string', $category->name, $name_string_name, $package );
				$category->description = apply_filters( 'wpml_translate_string', $category->description, $description_string_name, $package );
			}
		}
		return $categories;
	}

	/**
	 * @param int $term_id Term ID.
	 */
	public function created_or_edited_link_category_action( $term_id ) {
		$link_category = get_term( $term_id, 'link_category' );

		if ( $link_category ) {
			$package = $this->helper->get_package( $link_category, 'category' );
			$name_string_name = $this->helper->get_category_string_name( 'name', $link_category );
			$description_string_name = $this->helper->get_category_string_name( 'description', $link_category );

			do_action( 'wpml_register_string', $link_category->name, $name_string_name, $package, 'Link Category title', 'LINE');
			do_action( 'wpml_register_string', $link_category->description, $description_string_name, $package, 'Link Category description', 'AREA');
		}
	}

	/**
	 * @param int     $term         Term ID.
	 * @param int     $tt_id        Term taxonomy ID.
	 * @param string  $taxonomy     Taxonomy slug.
	 */
	public function delete_term_action( $term, $tt_id, $taxonomy ) {
		if ( 'link_category' === $taxonomy ) {
			$this->delete_strings_package( $term, 'category' );
		}
	}

}