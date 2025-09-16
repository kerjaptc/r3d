<?php
/**
 * Product Fields Module for R3DFPV EDD Addon Pack
 * Handles product variants and additional fields
 * 
 * @package R3DFPV_EDD_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class R3DFPV_Product_Fields {
    
    public function __construct() {
        // Add variant fields to quick post form
        add_action('r3dfpv_quickpost_extra_fields', [$this, 'add_variant_fields']);
        // Save variant data when product is created/updated
        add_action('r3dfpv_after_quickpost_create', [$this, 'save_variant_data'], 10, 2);
    }
    
    /**
     * Add variant fields to quick post form
     */
    public function add_variant_fields() {
        ?>
        <h2><?php _e('Product Variants', 'r3dfpv'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label><?php _e('Variants', 'r3dfpv'); ?></label></th>
                <td>
                    <div id="variants-container">
                        <div class="variant-row">
                            <h4><?php _e('Variant #1', 'r3dfpv'); ?></h4>
                            <p>
                                <label>
                                    <?php _e('Variant Name', 'r3dfpv'); ?>
                                    <input type="text" name="variants[0][name]" class="regular-text">
                                </label>
                            </p>
                            <p>
                                <label>
                                    <?php _e('Variant Price', 'r3dfpv'); ?>
                                    <input type="number" name="variants[0][price]" step="0.01" min="0" class="small-text">
                                    <span class="description"><?php _e('Leave empty to use main price', 'r3dfpv'); ?></span>
                                </label>
                            </p>
                            <p>
                                <label>
                                    <?php _e('Variant Image', 'r3dfpv'); ?>
                                    <input type="url" name="variants[0][image]" class="regular-text" placeholder="https://example.com/image.jpg">
                                    <button type="button" class="button upload-image"><?php _e('Upload', 'r3dfpv'); ?></button>
                                </label>
                            </p>
                            <p>
                                <label>
                                    <?php _e('Variant STL Files', 'r3dfpv'); ?>
                                    <input type="url" name="variants[0][stl_files][]" class="regular-text" placeholder="https://example.com/file.stl">
                                    <button type="button" class="button add-stl"><?php _e('Add Another URL', 'r3dfpv'); ?></button>
                                </label>
                            </p>
                        </div>
                    </div>
                    <button type="button" class="button" id="add-variant"><?php _e('Add Another Variant', 'r3dfpv'); ?></button>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save variant data when product is saved
     */
    public function save_variant_data($post_id, $data) {
        if (!empty($data['variants']) && is_array($data['variants'])) {
            $variants = [];
            foreach ($data['variants'] as $variant) {
                // Sanitize variant data
                $variant_data = [
                    'name' => sanitize_text_field($variant['name']),
                    'price' => !empty($variant['price']) ? floatval($variant['price']) : '',
                    'image' => esc_url_raw($variant['image']),
                    'stl_files' => []
                ];
                
                // Sanitize STL files
                if (!empty($variant['stl_files']) && is_array($variant['stl_files'])) {
                    foreach ($variant['stl_files'] as $stl_file) {
                        if (!empty($stl_file)) {
                            $variant_data['stl_files'][] = esc_url_raw($stl_file);
                        }
                    }
                }
                
                // Only add variant if it has a name or files
                if (!empty($variant_data['name']) || !empty($variant_data['stl_files'])) {
                    $variants[] = $variant_data;
                }
            }
            
            // Save variants as post meta
            if (!empty($variants)) {
                update_post_meta($post_id, '_r3dfpv_variants', $variants);
            }
        }
    }
}

// Initialize the module
new R3DFPV_Product_Fields();