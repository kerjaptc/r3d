<?php
/**
 * Plugin Name: R3DFPV EDD Addon Pack
 * Plugin URI: https://yourdomain.com
 * Description: Custom functionality for FPV Drone STL store - Quick post, custom taxonomy, external STL management, and more.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourdomain.com
 * License: GPL v2 or later
 * Text Domain: r3dfpv
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * 
 * @package R3DFPV_EDD_Addon
 */

// Jika file ini dipanggil langsung, keluar
if (!defined('ABSPATH')) {
    exit;
}

// Definisi constants untuk plugin
define('R3DFPV_PLUGIN_VERSION', '1.0.0');
define('R3DFPV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('R3DFPV_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('R3DFPV_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check jika EDD aktif
function r3dfpv_check_edd_dependency() {
    if (!class_exists('Easy_Digital_Downloads')) {
        add_action('admin_notices', 'r3dfpv_edd_missing_notice');
        return false;
    }
    return true;
}

// Notice jika EDD tidak aktif
function r3dfpv_edd_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <?php 
            printf(
                __('R3DFPV EDD Addon Pack requires Easy Digital Downloads to be installed and active. %s', 'r3dfpv'),
                '<a href="' . admin_url('plugin-install.php?s=Easy+Digital+Downloads&tab=search&type=term') . '">' . __('Install EDD now', 'r3dfpv') . '</a>'
            );
            ?>
        </p>
    </div>
    <?php
}

// Auto-load modul-modul plugin
function r3dfpv_autoload_modules() {
    $modules = [
        'admin-quickpost',
        'taxonomy-manager',
        'product-fields',
        'checkout-agreement',
        'seo-fields',
        'external-resources'
    ];
    
    foreach ($modules as $module) {
        $file_path = R3DFPV_PLUGIN_PATH . 'includes/module-' . $module . '.php';
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
}

// Fungsi untuk menjalankan migrasi taxonomy
function r3dfpv_run_taxonomy_migration() {
    // Pastikan file module taxonomy dimuat
    $taxonomy_file = R3DFPV_PLUGIN_PATH . 'includes/module-taxonomy-manager.php';
    
    if (file_exists($taxonomy_file)) {
        require_once $taxonomy_file;
        
        // Jalankan migrasi data hanya jika class tersedia
        if (class_exists('R3DFPV_Taxonomy_Manager')) {
            $taxonomy_manager = new R3DFPV_Taxonomy_Manager();
            if (method_exists($taxonomy_manager, 'migrate_old_meta_to_taxonomies')) {
                $taxonomy_manager->migrate_old_meta_to_taxonomies();
            }
        }
    }
}

// Hook untuk migrasi terjadwal
add_action('r3dfpv_scheduled_migration', 'r3dfpv_run_taxonomy_migration');

// Activation hook
function r3dfpv_plugin_activation() {
    // Schedule migrasi data untuk dijalankan nanti
    if (!wp_next_scheduled('r3dfpv_scheduled_migration')) {
        wp_schedule_single_event(time() + 10, 'r3dfpv_scheduled_migration');
    }
    
    // Setup default options atau table jika diperlukan
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'r3dfpv_plugin_activation');

// Deactivation hook
function r3dfpv_plugin_deactivation() {
    // Cleanup temporary data
    wp_clear_scheduled_hook('r3dfpv_scheduled_migration');
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'r3dfpv_plugin_deactivation');

// Inisialisasi plugin
function r3dfpv_init_plugin() {
    if (!r3dfpv_check_edd_dependency()) {
        return;
    }
    
    // Load textdomain untuk internationalization
    load_plugin_textdomain('r3dfpv', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Auto-load modules
    r3dfpv_autoload_modules();
    
    // Enqueue scripts dan styles
    add_action('admin_enqueue_scripts', 'r3dfpv_admin_scripts');
    add_action('wp_enqueue_scripts', 'r3dfpv_frontend_scripts');
}

// Admin scripts dan styles
function r3dfpv_admin_scripts($hook) {
    // Load scripts hanya di halaman yang diperlukan
    $allowed_pages = [
        'download_page_r3dfpv-quick-add',
        'download_page_edd-settings',
        'edit-tags.php',
        'term.php'
    ];
    
    if (!in_array($hook, $allowed_pages)) {
        return;
    }
    
    wp_enqueue_style('r3dfpv-admin', R3DFPV_PLUGIN_URL . 'assets/css/admin.css', [], R3DFPV_PLUGIN_VERSION);
    wp_enqueue_script('r3dfpv-admin', R3DFPV_PLUGIN_URL . 'assets/js/admin.js', ['jquery', 'jquery-ui-autocomplete'], R3DFPV_PLUGIN_VERSION, true);
    
    wp_localize_script('r3dfpv-admin', 'r3dfpv_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('r3dfpv_admin_nonce')
    ]);
}

// Frontend scripts dan styles
function r3dfpv_frontend_scripts() {
    if (!is_singular('download')) {
        return;
    }
    
    wp_enqueue_style('r3dfpv-frontend', R3DFPV_PLUGIN_URL . 'assets/css/frontend.css', [], R3DFPV_PLUGIN_VERSION);
    wp_enqueue_script('r3dfpv-frontend', R3DFPV_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], R3DFPV_PLUGIN_VERSION, true);
}

// Inisialisasi plugin
add_action('plugins_loaded', 'r3dfpv_init_plugin');

// Fungsi utilitas tambahan
if (!function_exists('r3dfpv_get_taxonomy_terms')) {
    function r3dfpv_get_taxonomy_terms($taxonomy, $args = []) {
        $defaults = [
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $terms = get_terms($args);
        
        if (is_wp_error($terms)) {
            return [];
        }
        
        return $terms;
    }
}

if (!function_exists('r3dfpv_log_message')) {
    function r3dfpv_log_message($message, $level = 'info') {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $timestamp = current_time('mysql');
        $log_entry = "[$timestamp] [$level] $message\n";
        
        $log_file = WP_CONTENT_DIR . '/r3dfpv-debug.log';
        
        error_log($log_entry, 3, $log_file);
    }
}
add_filter('get_edit_post_link', 'r3dfpv_redirect_edit_link', 10, 3);
function r3dfpv_redirect_edit_link($link, $post_id, $context) {
    $post = get_post($post_id);
    if ($post->post_type === 'download') {
        // Redirect ke custom admin page Anda
        return admin_url('edit.php?post_type=download&page=r3dfpv-quick-add&post=' . $post_id);
    }
    return $link;
}