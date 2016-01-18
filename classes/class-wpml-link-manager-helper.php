<?php

Class WPML_Link_Manager_Helper {

    private $package_type;

    /**
     * @param string $package_type
     */
    public function __construct( $package_type ) {
        $this->package_type = $package_type;
    }

    public function get_package_type() {
        return $this->package_type;
    }

    /**
     * @param int|object $link_or_cat
     * @param string     $subtype
     *
     * @return array $package
     */
    public function get_package( $link_or_cat, $subtype = 'link' ) {
        $package = null;
        $package_subtype = $this->package_type . ' - ' . $subtype;

        if ( is_object( $link_or_cat ) ) {
            if ( 'link' === $subtype ) {
                $package = array(
                    'kind'      => $package_subtype,
                    'name'      => $link_or_cat->link_id,
                    'title'     => $link_or_cat->link_name,
                    'edit_link' => admin_url( 'link.php?action=edit&link_id=' . $link_or_cat->link_id ),
                    'view_link' => $link_or_cat->link_url,
                );
            } elseif ( 'category' === $subtype ) {
                $package = array(
                    'kind' => $package_subtype,
                    'name' => $link_or_cat->term_id,
                    'title' => $link_or_cat->name,
                    'edit_link' => admin_url('edit-tags.php?action=edit&taxonomy=link_category&tag_ID=' . $link_or_cat->term_id),
                );
            }
        } else {
            $package = array(
                'kind' 		=> $package_subtype,
                'name' 		=> $link_or_cat,
            );
        }

        return $package;
    }

    /**
     * @param string $name
     * @param object $link
     *
     * @return string formatted
     */
    public function get_link_string_name( $name, $link ) {
        return 'link-' . $link->link_id . '-' . $name;
    }

    /**
     * @param string $name
     * @param object $category
     *
     * @return string formatted
     */
    public function get_category_string_name( $name, $category ) {
        return 'link-category-' . $category->term_id . '-' . $name;
    }

}