<?php
/**
 * Admin Quick Post Module for R3DFPV EDD Addon Pack
 * Provides a simplified interface for adding download products
 * 
 * @package R3DFPV_EDD_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class R3DFPV_Admin_Quick_Post {
    
    private $capability = 'edit_products';
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'handle_form_submission']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=download',
            __('Quick Add Product', 'r3dfpv'),
            __('Quick Add', 'r3dfpv'),
            $this->capability,
            'r3dfpv-quick-add',
            [$this, 'render_quick_add_page']
        );
    }
    
    /**
     * Render the quick add product form
     */
    public function render_quick_add_page() {
        // Check user capabilities
        if (!current_user_can($this->capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'r3dfpv'));
        }
        
        // Check jika sedang edit post
        $editing = false;
        $post_id = 0;
        $post = null;
        $title = '';
        $excerpt = '';
        $price = '';
        $stl_files = [];
        $stl_viewer_url = '';
        $product_type = '';
        $compatible_brands = '';
        $compatible_models = '';
        $print_settings = '';
        $files_included = '';
        
        if (isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            $editing = true;
            $post = get_post($post_id);
            
            if ($post) {
                $title = $post->post_title;
                $excerpt = $post->post_excerpt;
                $price = edd_get_download_price($post_id);
                $stl_files = get_post_meta($post_id, '_r3dfpv_stl_files', true);
                $stl_viewer_url = get_post_meta($post_id, '_r3dfpv_stl_viewer_url', true);
                $print_settings = get_post_meta($post_id, '_r3dfpv_print_settings', true);
                $files_included = get_post_meta($post_id, '_r3dfpv_files_included', true);
                
                // Get taxonomies
                $product_type_terms = wp_get_object_terms($post_id, 'r3d_product_type');
                if (!empty($product_type_terms) && !is_wp_error($product_type_terms)) {
                    $product_type = $product_type_terms[0]->slug;
                }
                
                $brand_terms = wp_get_object_terms($post_id, 'r3d_compatible_brand');
                if (!empty($brand_terms) && !is_wp_error($brand_terms)) {
                    $compatible_brands = implode(', ', wp_list_pluck($brand_terms, 'name'));
                }
                
                $model_terms = wp_get_object_terms($post_id, 'r3d_compatible_model');
                if (!empty($model_terms) && !is_wp_error($model_terms)) {
                    $compatible_models = implode(', ', wp_list_pluck($model_terms, 'name'));
                }
            }
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo $editing ? __('Edit Product', 'r3dfpv') : __('Quick Add Product', 'r3dfpv'); ?></h1>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo $editing ? __('Product updated successfully!', 'r3dfpv') : __('Product created successfully!', 'r3dfpv'); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html(urldecode($_GET['error'])); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('r3dfpv_quick_add_product', 'r3dfpv_nonce'); ?>
                
                <?php if ($editing): ?>
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <?php endif; ?>
                
                <h2><?php _e('Basic Information', 'r3dfpv'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="product_title"><?php _e('Product Title', 'r3dfpv'); ?> *</label></th>
                        <td>
                            <input type="text" id="product_title" name="product_title" class="regular-text" value="<?php echo esc_attr($title); ?>" required>
                            <p class="description"><?php _e('The name of your product', 'r3dfpv'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="product_excerpt"><?php _e('Short Description', 'r3dfpv'); ?></label></th>
                        <td>
                            <textarea id="product_excerpt" name="product_excerpt" rows="3" class="large-text"><?php echo esc_textarea($excerpt); ?></textarea>
                            <p class="description"><?php _e('A brief description of your product', 'r3dfpv'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="product_price"><?php _e('Price', 'r3dfpv'); ?> *</label></th>
                        <td>
                            <input type="number" id="product_price" name="product_price" step="0.01" min="0" value="<?php echo esc_attr($price); ?>" required>
                            <p class="description"><?php _e('Product price in your currency', 'r3dfpv'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h2><?php _e('STL Files', 'r3dfpv'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="stl_files"><?php _e('STL File URLs', 'r3dfpv'); ?></label></th>
                        <td>
                            <div id="stl-urls-container">
                                <?php if (!empty($stl_files) && is_array($stl_files)): ?>
                                    <?php foreach ($stl_files as $stl_file): ?>
                                        <div class="stl-url-input">
                                            <input type="url" name="stl_files[]" class="regular-text" placeholder="https://example.com/file.stl" value="<?php echo esc_url($stl_file); ?>">
                                            <button type="button" class="button remove-stl-url"><?php _e('Remove', 'r3dfpv'); ?></button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="stl-url-input">
                                        <input type="url" name="stl_files[]" class="regular-text" placeholder="https://example.com/file.stl">
                                        <button type="button" class="button remove-stl-url"><?php _e('Remove', 'r3dfpv'); ?></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button" id="add-stl-url"><?php _e('Add Another URL', 'r3dfpv'); ?></button>
                            <p class="description"><?php _e('External URLs to your STL files. HTTPS required.', 'r3dfpv'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="stl_viewer_url"><?php _e('STL Viewer URL', 'r3dfpv'); ?></label></th>
                        <td>
                            <input type="url" id="stl_viewer_url" name="stl_viewer_url" class="regular-text" placeholder="https://viewer.example.com/?file=xxx" value="<?php echo esc_url($stl_viewer_url); ?>">
                            <p class="description"><?php _e('URL to external STL viewer (optional)', 'r3dfpv'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h2><?php _e('Product Taxonomy', 'r3dfpv'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="product_type"><?php _e('Product Type', 'r3dfpv'); ?></label></th>
                        <td>
                            <?php
                            // Get terms from taxonomy
                            $terms = get_terms(['taxonomy' => 'r3d_product_type', 'hide_empty' => false]);
                            
                            // Remove duplicate terms by slug
                            $unique_terms = [];
                            if (!is_wp_error($terms) && !empty($terms)) {
                                foreach ($terms as $term) {
                                    if (!isset($unique_terms[$term->slug])) {
                                        $unique_terms[$term->slug] = $term;
                                    }
                                }
                            }
                            ?>
                            <select id="product_type" name="product_type">
                                <option value=""><?php _e('Select a type', 'r3dfpv'); ?></option>
                                <?php
                                if (!empty($unique_terms)) {
                                    foreach ($unique_terms as $term) {
                                        ?>
                                        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($product_type, $term->slug); ?>>
                                            <?php echo esc_html($term->name); ?>
                                        </option>
                                        <?php
                                    }
                                } else {
                                    echo '<option value="">' . __('No terms found', 'r3dfpv') . '</option>';
                                }
                                ?>
                            </select>
                            <p class="description"><?php _e('Choose a product type', 'r3dfpv'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="compatible_brands"><?php _e('Compatible Brands', 'r3dfpv'); ?></label></th>
                        <td>
                            <input type="text" id="compatible_brands" name="compatible_brands" class="regular-text" placeholder="DJI, GoPro, SpeedyBee" value="<?php echo esc_attr($compatible_brands); ?>">
                            <p class="description"><?php _e('Comma-separated brands. New brands will be created as needed.', 'r3dfpv'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="compatible_models"><?php _e('Compatible Models', 'r3dfpv'); ?></label></th>
                        <td>
                            <input type="text" id="compatible_models" name="compatible_models" class="regular-text" placeholder="DJI O3, GoPro Hero10, SpeedyBee Mario5" value="<?php echo esc_attr($compatible_models); ?>">
                            <p class="description"><?php _e('Comma-separated models. New models will be created as needed.', 'r3dfpv'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php
                /**
                 * Hook: r3dfpv_quickpost_extra_fields
                 * Allows other modules to inject extra form fields (e.g. product variants)
                 */
                do_action('r3dfpv_quickpost_extra_fields');
                ?>
                
                <h2><?php _e('Additional Information', 'r3dfpv'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="print_settings"><?php _e('Print Settings', 'r3dfpv'); ?></label></th>
                        <td>
                            <textarea id="print_settings" name="print_settings" rows="3" class="large-text"><?php echo esc_textarea($print_settings ? $print_settings : "Material: TPU\nLayer Height: 0.2mm\nInfill: 20%\nSupport: No"); ?></textarea>
                            <p class="description"><?php _e('Recommended print settings for this STL', 'r3dfpv'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="files_included"><?php _e('Files Included', 'r3dfpv'); ?></label></th>
                        <td>
                            <textarea id="files_included" name="files_included" rows="3" class="large-text" placeholder="Antenna mount.stl
XT60 mount.stl
GPS mount.stl
Personal-use.txt"><?php echo esc_textarea($files_included); ?></textarea>
                            <p class="description"><?php _e('List of files included in this product', 'r3dfpv'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button($editing ? __('Update Product', 'r3dfpv') : __('Create Product', 'r3dfpv'), 'primary', 'submit_product'); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Handle form submission
     */
    public function handle_form_submission() {
        if (!isset($_POST['r3dfpv_nonce']) || !isset($_POST['submit_product'])) {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['r3dfpv_nonce'], 'r3dfpv_quick_add_product')) {
            wp_die(__('Security check failed', 'r3dfpv'));
        }
        
        // Check user capabilities
        if (!current_user_can($this->capability)) {
            wp_die(__('You do not have sufficient permissions to perform this action.', 'r3dfpv'));
        }
        
        // Validate and sanitize input
        $title = sanitize_text_field($_POST['product_title']);
        $excerpt = sanitize_textarea_field($_POST['product_excerpt']);
        $price = floatval($_POST['product_price']);
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (empty($title) || empty($price)) {
            $error_url = add_query_arg([
                'error' => urlencode(__('Product title and price are required.', 'r3dfpv'))
            ], admin_url('edit.php?post_type=download&page=r3dfpv-quick-add'));
            wp_redirect($error_url);
            exit;
        }
        
        // Prepare post data
        $post_data = [
            'post_title'   => $title,
            'post_excerpt' => $excerpt,
            'post_content' => $this->generate_product_content($_POST),
            'post_status'  => 'publish',
            'post_type'    => 'download',
        ];
        
        // If we're editing, add the ID
        if ($post_id) {
            $post_data['ID'] = $post_id;
        }
        
        // Create or update the product
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            $error_url = add_query_arg([
                'error' => urlencode($post_id->get_error_message())
            ], admin_url('edit.php?post_type=download&page=r3dfpv-quick-add'));
            wp_redirect($error_url);
            exit;
        }
        
        // Set product meta
        update_post_meta($post_id, 'edd_price', $price);
        
        // Handle STL files
        if (!empty($_POST['stl_files'])) {
            $stl_files = array_map('esc_url_raw', $_POST['stl_files']);
            update_post_meta($post_id, '_r3dfpv_stl_files', array_filter($stl_files));
        } else {
            delete_post_meta($post_id, '_r3dfpv_stl_files');
        }
        
        // Handle STL viewer URL
        if (!empty($_POST['stl_viewer_url'])) {
            update_post_meta($post_id, '_r3dfpv_stl_viewer_url', esc_url_raw($_POST['stl_viewer_url']));
        } else {
            delete_post_meta($post_id, '_r3dfpv_stl_viewer_url');
        }
        
        // Handle print settings
        if (!empty($_POST['print_settings'])) {
            update_post_meta($post_id, '_r3dfpv_print_settings', sanitize_textarea_field($_POST['print_settings']));
        } else {
            delete_post_meta($post_id, '_r3dfpv_print_settings');
        }
        
        // Handle files included
        if (!empty($_POST['files_included'])) {
            update_post_meta($post_id, '_r3dfpv_files_included', sanitize_textarea_field($_POST['files_included']));
        } else {
            delete_post_meta($post_id, '_r3dfpv_files_included');
        }
        
        // Handle taxonomies: set terms on the 'download' post type
        // Product Type (single select from existing taxonomy terms)
        if (!empty($_POST['product_type'])) {
            $term_slug = sanitize_text_field($_POST['product_type']);
            $term = get_term_by('slug', $term_slug, 'r3d_product_type');
            if ($term && !is_wp_error($term)) {
                wp_set_object_terms($post_id, intval($term->term_id), 'r3d_product_type', false);
            } else {
                // Attempt to create term if it doesn't exist
                $created = wp_insert_term(sanitize_text_field($term_slug), 'r3d_product_type', ['slug' => $term_slug]);
                if (!is_wp_error($created) && !empty($created['term_id'])) {
                    wp_set_object_terms($post_id, intval($created['term_id']), 'r3d_product_type', false);
                }
            }
        } else {
            wp_delete_object_term_relationships($post_id, 'r3d_product_type');
        }

        // Compatible Brands (comma-separated input)
        if (!empty($_POST['compatible_brands'])) {
            $raw = sanitize_text_field($_POST['compatible_brands']);
            $brands = array_filter(array_map('trim', explode(',', $raw)));
            $brands = array_map('sanitize_text_field', $brands);
            if (!empty($brands)) {
                // Create terms as needed and assign
                $term_ids = [];
                foreach ($brands as $b) {
                    $term = term_exists($b, 'r3d_compatible_brand');
                    if (!$term) {
                        $term = wp_insert_term($b, 'r3d_compatible_brand', ['slug' => sanitize_title($b)]);
                    }
                    if (!is_wp_error($term)) {
                        $term_ids[] = is_array($term) ? $term['term_id'] : $term;
                    }
                }
                wp_set_object_terms($post_id, $term_ids, 'r3d_compatible_brand', false);
            }
        } else {
            wp_delete_object_term_relationships($post_id, 'r3d_compatible_brand');
        }

        // Compatible Models (comma-separated input)
        if (!empty($_POST['compatible_models'])) {
            $raw = sanitize_text_field($_POST['compatible_models']);
            $models = array_filter(array_map('trim', explode(',', $raw)));
            $models = array_map('sanitize_text_field', $models);
            if (!empty($models)) {
                // Create terms as needed and assign
                $term_ids = [];
                foreach ($models as $m) {
                    $term = term_exists($m, 'r3d_compatible_model');
                    if (!$term) {
                        $term = wp_insert_term($m, 'r3d_compatible_model', ['slug' => sanitize_title($m)]);
                    }
                    if (!is_wp_error($term)) {
                        $term_ids[] = is_array($term) ? $term['term_id'] : $term;
                    }
                }
                wp_set_object_terms($post_id, $term_ids, 'r3d_compatible_model', false);
            }
        } else {
            wp_delete_object_term_relationships($post_id, 'r3d_compatible_model');
        }
        
        // Redirect with success message
        $success_url = add_query_arg([
            'success' => '1',
            'post' => $post_id
        ], admin_url('edit.php?post_type=download&page=r3dfpv-quick-add'));
        
        // Allow other modules to act after creation (e.g. save variants)
        do_action('r3dfpv_after_quickpost_create', $post_id, $_POST);

        wp_redirect($success_url);
        exit;
    }
    
    /**
     * Generate product content from form data
     */
    private function generate_product_content($data) {
        $content = '';
        
        if (!empty($data['product_excerpt'])) {
            $content .= '<p>' . esc_html($data['product_excerpt']) . '</p>';
        }
        
        if (!empty($data['print_settings'])) {
            $content .= '<h3>' . __('Print Settings', 'r3dfpv') . '</h3>';
            $content .= '<pre>' . esc_html($data['print_settings']) . '</pre>';
        }
        
        if (!empty($data['files_included'])) {
            $content .= '<h3>' . __('Files Included', 'r3dfpv') . '</h3>';
            $content .= '<ul>';
            $files = explode("\n", str_replace("\r", "", $data['files_included']));
            foreach ($files as $file) {
                if (!empty(trim($file))) {
                    $content .= '<li>' . esc_html(trim($file)) . '</li>';
                }
            }
            $content .= '</ul>';
        }
        
        return $content;
    }
    
    /**
     * Localize script dengan translatable strings
     */
    public function localize_admin_script() {
        wp_localize_script('r3dfpv-admin-quickpost', 'r3dfpv_admin_i18n', [
            'remove_text' => __('Remove', 'r3dfpv'),
            'title_required' => __('Product title is required.', 'r3dfpv'),
            'price_required' => __('Valid price is required.', 'r3dfpv'),
            'url_required' => __('At least one STL file URL is required.', 'r3dfpv'),
            'invalid_url' => __('Please enter valid HTTPS URLs for STL files.', 'r3dfpv'),
            'invalid_url_format' => __('Please enter a valid HTTPS URL.', 'r3dfpv'),
            'errors_found' => __('Please fix the following errors:', 'r3dfpv'),
            'show_advanced' => __('Show Advanced Options', 'r3dfpv'),
            'hide_advanced' => __('Hide Advanced Options', 'r3dfpv')
        ]);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'download_page_r3dfpv-quick-add') {
            return;
        }
        
        wp_enqueue_script(
            'r3dfpv-admin-quickpost', 
            R3DFPV_PLUGIN_URL . 'assets/js/admin-quickpost.js', 
            ['jquery', 'jquery-ui-autocomplete'], 
            R3DFPV_PLUGIN_VERSION, 
            true
        );
        
        // Localize script
        $this->localize_admin_script();
        
        wp_enqueue_style(
            'r3dfpv-admin-quickpost', 
            R3DFPV_PLUGIN_URL . 'assets/css/admin-quickpost.css', 
            [], 
            R3DFPV_PLUGIN_VERSION
        );
    }
}

// Initialize the module
new R3DFPV_Admin_Quick_Post();