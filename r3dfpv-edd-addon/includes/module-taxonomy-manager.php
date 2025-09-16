<?php
/**
 * Taxonomy Manager Module for R3DFPV EDD Addon Pack
 * Handles custom taxonomies for FPV Drone products
 * 
 * @package R3DFPV_EDD_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class R3DFPV_Taxonomy_Manager {
    
    public function __construct() {
        add_action('init', [$this, 'register_custom_taxonomies']);
        add_action('admin_menu', [$this, 'adjust_taxonomy_menu_placement']);
        add_filter('parent_file', [$this, 'highlight_taxonomy_menu']);
    }
    
    /**
     * Register all custom taxonomies
     */
    public function register_custom_taxonomies() {
        $this->register_product_type_taxonomy();
        $this->register_compatible_brand_taxonomy();
        $this->register_compatible_model_taxonomy();
    }
    
    /**
     * Register Product Type taxonomy (hierarchical - like categories)
     */
    private function register_product_type_taxonomy() {
        $labels = array(
            'name'              => _x('Product Types', 'taxonomy general name', 'r3dfpv'),
            'singular_name'     => _x('Product Type', 'taxonomy singular name', 'r3dfpv'),
            'search_items'      => __('Search Product Types', 'r3dfpv'),
            'all_items'         => __('All Product Types', 'r3dfpv'),
            'parent_item'       => __('Parent Product Type', 'r3dfpv'),
            'parent_item_colon' => __('Parent Product Type:', 'r3dfpv'),
            'edit_item'         => __('Edit Product Type', 'r3dfpv'),
            'update_item'       => __('Update Product Type', 'r3dfpv'),
            'add_new_item'      => __('Add New Product Type', 'r3dfpv'),
            'new_item_name'     => __('New Product Type Name', 'r3dfpv'),
            'menu_name'         => __('Product Types', 'r3dfpv'),
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_menu'      => true,
            'show_in_nav_menus' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'product-type'),
        );
        
        register_taxonomy('r3d_product_type', 'download', $args);
    }
    
    /**
     * Register Compatible Brand taxonomy (non-hierarchical - like tags)
     */
    private function register_compatible_brand_taxonomy() {
        $labels = array(
            'name'              => _x('Compatible Brands', 'taxonomy general name', 'r3dfpv'),
            'singular_name'     => _x('Compatible Brand', 'taxonomy singular name', 'r3dfpv'),
            'search_items'      => __('Search Compatible Brands', 'r3dfpv'),
            'all_items'         => __('All Compatible Brands', 'r3dfpv'),
            'edit_item'         => __('Edit Compatible Brand', 'r3dfpv'),
            'update_item'       => __('Update Compatible Brand', 'r3dfpv'),
            'add_new_item'      => __('Add New Compatible Brand', 'r3dfpv'),
            'new_item_name'     => __('New Compatible Brand Name', 'r3dfpv'),
            'menu_name'         => __('Compatible Brands', 'r3dfpv'),
        );
        
        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_menu'      => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'compatible-brand'),
        );
        
        register_taxonomy('r3d_compatible_brand', 'download', $args);
    }
    
    /**
     * Register Compatible Model taxonomy (non-hierarchical - like tags)
     */
    private function register_compatible_model_taxonomy() {
        $labels = array(
            'name'              => _x('Compatible Models', 'taxonomy general name', 'r3dfpv'),
            'singular_name'     => _x('Compatible Model', 'taxonomy singular name', 'r3dfpv'),
            'search_items'      => __('Search Compatible Models', 'r3dfpv'),
            'all_items'         => __('All Compatible Models', 'r3dfpv'),
            'edit_item'         => __('Edit Compatible Model', 'r3dfpv'),
            'update_item'       => __('Update Compatible Model', 'r3dfpv'),
            'add_new_item'      => __('Add New Compatible Model', 'r3dfpv'),
            'new_item_name'     => __('New Compatible Model Name', 'r3dfpv'),
            'menu_name'         => __('Compatible Models', 'r3dfpv'),
        );
        
        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_menu'      => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'compatible-model'),
        );
        
        register_taxonomy('r3d_compatible_model', 'download', $args);
    }
    
    /**
     * Adjust taxonomy menu placement to appear under Downloads menu
     */
    public function adjust_taxonomy_menu_placement() {
        // Add Product Types under Downloads menu
        add_submenu_page(
            'edit.php?post_type=download',
            __('Product Types', 'r3dfpv'),
            __('Product Types', 'r3dfpv'),
            'manage_categories',
            'edit-tags.php?taxonomy=r3d_product_type&post_type=download'
        );
        
        // Add Compatible Brands under Downloads menu
        add_submenu_page(
            'edit.php?post_type=download',
            __('Compatible Brands', 'r3dfpv'),
            __('Compatible Brands', 'r3dfpv'),
            'manage_categories',
            'edit-tags.php?taxonomy=r3d_compatible_brand&post_type=download'
        );
        
        // Add Compatible Models under Downloads menu
        add_submenu_page(
            'edit.php?post_type=download',
            __('Compatible Models', 'r3dfpv'),
            __('Compatible Models', 'r3dfpv'),
            'manage_categories',
            'edit-tags.php?taxonomy=r3d_compatible_model&post_type=download'
        );
    }
    
    /**
     * Ensure the correct menu is highlighted when editing taxonomies
     */
    public function highlight_taxonomy_menu($parent_file) {
        global $current_screen;
        
        $taxonomy = $current_screen->taxonomy;
        
        if ($taxonomy == 'r3d_product_type' || 
            $taxonomy == 'r3d_compatible_brand' || 
            $taxonomy == 'r3d_compatible_model') {
            $parent_file = 'edit.php?post_type=download';
        }
        
        return $parent_file;
    }
}
add_filter('edd_download_supports', 'r3dfpv_edd_custom_supports');
function r3dfpv_edd_custom_supports($supports) {
    // Tambahkan dukungan untuk features yang diperlukan
    $supports[] = 'custom-fields';
    $supports[] = 'author';
    // Hapus dukungan untuk editor bawaan jika ingin menggunakan custom form
    // $key = array_search('editor', $supports);
    // if ($key !== false) {
    //     unset($supports[$key]);
    // }
    return $supports;
}

// Initialize the module
new R3DFPV_Taxonomy_Manager();