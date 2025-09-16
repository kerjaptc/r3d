<?php
/**
 * Checkout Agreement Module for R3DFPV EDD Addon Pack
 * Adds license agreement to checkout
 * 
 * @package R3DFPV_EDD_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class R3DFPV_Checkout_Agreement {
    
    public function __construct() {
        // Add agreement checkbox to checkout
        add_action('edd_purchase_form_before_submit', [$this, 'add_agreement_checkbox']);
        // Validate agreement on checkout
        add_action('edd_checkout_error_checks', [$this, 'validate_agreement']);
    }
    
    /**
     * Add agreement checkbox to checkout
     */
    public function add_agreement_checkbox() {
        ?>
        <fieldset id="edd_agree_terms">
            <legend><?php _e('License Agreement', 'r3dfpv'); ?></legend>
            <p>
                <input type="checkbox" name="edd_agree_license" id="edd_agree_license" value="1">
                <label for="edd_agree_license">
                    <?php _e('I agree to use these files for personal use only. Redistribution or commercial use is prohibited.', 'r3dfpv'); ?>
                </label>
            </p>
        </fieldset>
        <?php
    }
    
    /**
     * Validate agreement on checkout
     */
    public function validate_agreement($valid_data) {
        if (!isset($_POST['edd_agree_license'])) {
            edd_set_error('agree_license', __('You must agree to the license terms.', 'r3dfpv'));
        }
    }
}

// Initialize the module
new R3DFPV_Checkout_Agreement();