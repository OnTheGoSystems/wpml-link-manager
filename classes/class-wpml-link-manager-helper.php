<?php

Class WPML_Link_Manager_Helper {

    private $package_type;

    /**
     * Constructor
     */
    function __construct( $package_type ) {
        $this->package_type = $package_type;
    }


    /**
     * Returns a string package
     *
     * @param mixed int|object $link or $category
     * @param string $subtype
     *
     * @return array $package
     */
    public function get_package( $link_or_cat, $subtype = 'link' ) {

        $package_subtype = $this->package_type . ' - ' . $subtype;

        /**
         * More data if object
         * This is important when we are registering
         * the package in the database
         */
        if ( is_object( $link_or_cat ) && 'link' === $subtype ) {
            $package = array(
                'kind'      => $package_subtype,
                'name'      => $link_or_cat->link_id,
                'title'     => $link_or_cat->link_name,
                'edit_link' => admin_url( 'link.php?action=edit&link_id=' . $link_or_cat->link_id ),
                'view_link' => $link_or_cat->link_url,
            );
        } elseif ( is_object( $link_or_cat ) && 'category' === $subtype ) {
            $package = array(
                'kind'      => $package_subtype,
                'name'      => $link_or_cat->term_id,
                'title'     => $link_or_cat->name,
                'edit_link' => admin_url( 'edit-tags.php?action=edit&taxonomy=link_category&tag_ID=' . $link_or_cat->term_id ),
            );
        } else {
            $package = array(
                'kind' 		=> $package_subtype,
                'name' 		=> $link_or_cat,
            );
        }

        return $package;
    }


    /**
     * Format the link string name including the link ID
     *
     * @param string $name
     * @param object $link
     *
     * @return string formatted
     */
    public function get_link_string_name( $name, $link ) {
        return 'link-' . $link->link_id . '-' . $name;
    }


    /**
     * Format the category string name including the term ID
     *
     * @param string $name
     * @param object $category
     *
     * @return string formatted
     */
    public function get_category_string_name( $name, $category ) {
        return 'link-category-' . $category->term_id . '-' . $name;
    }

}