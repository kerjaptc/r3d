<?php
/**
 * External Resources Module for R3DFPV EDD Addon Pack
 * Handles external STL files and viewer integration
 * 
 * @package R3DFPV_EDD_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class R3DFPV_External_Resources {
    
    public function __construct() {
        // Validate STL URLs before saving
        add_filter('r3dfpv_before_save_stl_files', [$this, 'validate_stl_urls']);
        // Add STL viewer to product page
        add_action('edd_after_download_content', [$this, 'display_stl_viewer']);
    }
    
    /**
     * Validate STL URLs
     */
    public function validate_stl_urls($urls) {
        $valid_urls = [];
        foreach ($urls as $url) {
            if ($this->is_valid_stl_url($url)) {
                $valid_urls[] = $url;
            }
        }
        return $valid_urls;
    }
    
    /**
     * Check if a URL is a valid STL file
     */
    private function is_valid_stl_url($url) {
        // Check if it's a valid HTTPS URL
        if (!wp_http_validate_url($url) || strpos($url, 'https://') !== 0) {
            return false;
        }
        
        // Check if it points to an STL file (by extension)
        $path = parse_url($url, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        return in_array(strtolower($extension), ['stl']);
    }
    
    /**
     * Display STL viewer on product page
     */
    public function display_stl_viewer($download_id) {
        $stl_files = get_post_meta($download_id, '_r3dfpv_stl_files', true);
        $viewer_url = get_post_meta($download_id, '_r3dfpv_stl_viewer_url', true);
        
        if (!empty($stl_files) && is_array($stl_files)) {
            echo '<div class="r3dfpv-stl-viewer">';
            echo '<h3>' . __('3D Preview', 'r3dfpv') . '</h3>';
            
            if (!empty($viewer_url)) {
                // Use external viewer
                echo '<iframe src="' . esc_url($viewer_url) . '" width="100%" height="500px" frameborder="0"></iframe>';
            } else {
                // Default viewer implementation
                echo '<p>' . __('STL files are available for download.', 'r3dfpv') . '</p>';
                echo '<ul>';
                foreach ($stl_files as $file) {
                    echo '<li><a href="' . esc_url($file) . '" download>' . __('Download STL File', 'r3dfpv') . '</a></li>';
                }
                echo '</ul>';
            }
            
            echo '</div>';
        }
    }
}

// Initialize the module
new R3DFPV_External_Resources();