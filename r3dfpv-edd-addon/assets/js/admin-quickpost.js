/**
 * Admin Quick Post JavaScript for R3DFPV EDD Addon Pack
 * Handles dynamic URL fields and form interactions
 * 
 * @package R3DFPV_EDD_Addon
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Container untuk URL inputs
    var $stlUrlsContainer = $('#stl-urls-container');
    
    /**
     * Tambah input URL baru
     */
    $('#add-stl-url').on('click', function(e) {
        e.preventDefault();
        
        var $newInput = $(
            '<div class="stl-url-input" style="margin-bottom: 10px;">' +
            '<input type="url" name="stl_files[]" class="regular-text" placeholder="https://example.com/file.stl" style="margin-right: 10px;">' +
            '<button type="button" class="button remove-stl-url" style="color: #dc3232;">' + r3dfpv_admin_i18n.remove_text + '</button>' +
            '</div>'
        );
        
        $stlUrlsContainer.append($newInput);
        
        // Attach event handler untuk remove button yang baru
        $newInput.find('.remove-stl-url').on('click', function() {
            $(this).closest('.stl-url-input').remove();
        });
    });
    
    /**
     * Handle remove URL buttons (untuk yang sudah ada di halaman)
     */
    $('.remove-stl-url').on('click', function() {
        $(this).closest('.stl-url-input').remove();
    });
    
    /**
     * Validasi form sebelum submit
     */
    $('form').on('submit', function(e) {
        var isValid = true;
        var errorMessages = [];
        
        // Validasi judul produk
        var $title = $('#product_title');
        if ($.trim($title.val()) === '') {
            isValid = false;
            errorMessages.push(r3dfpv_admin_i18n.title_required);
            $title.addClass('error-field');
        } else {
            $title.removeClass('error-field');
        }
        
        // Validasi harga
        var $price = $('#product_price');
        var priceVal = parseFloat($price.val());
        if (isNaN(priceVal) || priceVal <= 0) {
            isValid = false;
            errorMessages.push(r3dfpv_admin_i18n.price_required);
            $price.addClass('error-field');
        } else {
            $price.removeClass('error-field');
        }
        
        // Validasi URL STL (minimal satu URL)
        var $stlUrls = $('input[name="stl_files[]"]');
        var hasValidUrl = false;
        
        $stlUrls.each(function() {
            var url = $.trim($(this).val());
            if (url !== '') {
                // Validasi format URL
                if (!isValidUrl(url)) {
                    isValid = false;
                    errorMessages.push(r3dfpv_admin_i18n.invalid_url);
                    $(this).addClass('error-field');
                } else {
                    hasValidUrl = true;
                    $(this).removeClass('error-field');
                }
            }
        });
        
        if (!hasValidUrl) {
            isValid = false;
            errorMessages.push(r3dfpv_admin_i18n.url_required);
        }
        
        // Jika ada error, tampilkan pesan
        if (!isValid) {
            e.preventDefault();
            
            // Hapus notifikasi error sebelumnya
            $('.r3dfpv-error-notice').remove();
            
            // Buat notifikasi error
            var errorHtml = '<div class="notice notice-error is-dismissible r3dfpv-error-notice" style="margin-top: 20px;">' +
                '<p><strong>' + r3dfpv_admin_i18n.errors_found + '</strong></p>' +
                '<ul>';
            
            $.each(errorMessages, function(index, message) {
                errorHtml += '<li>' + message + '</li>';
            });
            
            errorHtml += '</ul></div>';
            
            // Tambahkan notifikasi di atas form
            $('.wrap h1').after(errorHtml);
            
            // Scroll ke atas
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        }
    });
    
    /**
     * Validasi format URL
     */
    function isValidUrl(url) {
        try {
            new URL(url);
            return url.startsWith('https://');
        } catch (e) {
            return false;
        }
    }
    
    /**
     * Auto-suggest untuk field tags
     */
    var availableTags = [
        'gopro-mount', 'antenna-mount', 'tpu', 'arm-guard', 
        'landing-skid', 'side-cover', 'gps-mount', 'lens-protector',
        'cinewhoop', 'tinywhoop', 'freestyle', 'racing', 'micro-drone',
        'camera-mount', 'battery-strap', 'xt60-mount', 'action-cam',
        'fpv-accessory', '3d-print', 'drone-part', 'custom-mount'
    ];
    
    $('#product_tags').autocomplete({
        source: availableTags,
        delay: 100,
        minLength: 2,
        autoFocus: true
    });
    
    /**
     * Toggle advanced options
     */
    $('.toggle-advanced').on('click', function(e) {
        e.preventDefault();
        $('.advanced-options').toggleClass('hidden');
        $(this).text(function(i, text) {
            return text === r3dfpv_admin_i18n.show_advanced ? 
                r3dfpv_admin_i18n.hide_advanced : 
                r3dfpv_admin_i18n.show_advanced;
        });
    });
    
    /**
     * Preview URL validation
     */
    $('input[name="stl_files[]"]').on('blur', function() {
        var url = $.trim($(this).val());
        if (url !== '' && !isValidUrl(url)) {
            $(this).addClass('error-field');
            $(this).after('<span class="error-message" style="color: #dc3232; display: block;">' + 
                r3dfpv_admin_i18n.invalid_url_format + '</span>');
        } else {
            $(this).removeClass('error-field');
            $(this).next('.error-message').remove();
        }
    });
});