<?php
/**
 * SEO Fields Module for R3DFPV EDD Addon Pack
 * Adds SEO fields to products
 * 
 * @package R3DFPV_EDD_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class R3DFPV_SEO_Fields {
    
    public function __construct() {
        // Add SEO fields to quick post form
        add_action('r3dfpv_quickpost_extra_fields', [$this, 'add_seo_fields']);
        // Save SEO data when product is saved
        add_action('r3dfpv_after_quickpost_create', [$this, 'save_seo_data'], 10, 2);
    }
    
    /**
     * Add SEO fields to quick post form
     */
    public function add_seo_fields() {
        ?>
        <h2><?php _e('SEO Settings', 'r3dfpv'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="meta_title"><?php _e('Meta Title', 'r3dfpv'); ?></label></th>
                <td>
                    <input type="text" id="meta_title" name="meta_title" class="regular-text">
                    <p class="description"><?php _e('Title for search engines (optional)', 'r3dfpv'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="meta_description"><?php _e('Meta Description', 'r3dfpv'); ?></label></th>
                <td>
                    <textarea id="meta_description" name="meta_description" rows="3" class="large-text"></textarea>
                    <p class="description"><?php _e('Description for search engines (optional)', 'r3dfpv'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save SEO data when product is saved
     */
    public function save_seo_data($post_id, $data) {
        if (!empty($data['meta_title'])) {
            update_post_meta($post_id, '_r3dfpv_meta_title', sanitize_text_field($data['meta_title']));
        }
        
        if (!empty($data['meta_description'])) {
            update_post_meta($post_id, '_r3dfpv_meta_description', sanitize_textarea_field($data['meta_description']));
        }
    }
}

// Initialize the module
new R3DFPV_SEO_Fields();