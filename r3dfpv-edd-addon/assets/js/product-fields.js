(function($){
    $(document).ready(function(){
        var variantIndex = 0;

        function addVariant(prefill) {
            var tpl = $('#r3dfpv-variant-template').html();
            var html = tpl.replace(/__index__/g, variantIndex);
            var $el = $(html);
            $('#r3dfpv-variants-container').append($el);
            variantIndex++;
            return $el;
        }

        // Add initial variant row for convenience
        $('#r3dfpv-add-variant').on('click', function(e){
            e.preventDefault();
            addVariant();
        });

        // Delegate remove variant
        $(document).on('click', '.r3dfpv-remove-variant', function(e){
            e.preventDefault();
            $(this).closest('.r3dfpv-variant').remove();
        });

        // Add variant STL URL
        $(document).on('click', '.r3dfpv-add-variant-stl', function(e){
            e.preventDefault();
            var $container = $(this).closest('.r3dfpv-variant').find('.r3dfpv-variant-stl-list');
            var idx = Date.now();
            var input = '<div class="r3dfpv-variant-stl-item">' +
                '<input type="url" name="r3dfpv_variants['+idx+'][stl_files][]" class="regular-text" placeholder="https://example.com/file.stl" />' +
                '<button type="button" class="button r3dfpv-remove-stl-url">' + r3dfpv_product_fields_i18n.remove_variant + '</button>' +
                '</div>';
            $container.append(input);
        });

        // Remove variant STL URL
        $(document).on('click', '.r3dfpv-remove-stl-url', function(e){
            e.preventDefault();
            $(this).closest('.r3dfpv-variant-stl-item').remove();
        });

        // Basic client-side validation on submit
        $('form').on('submit', function(e){
            var invalid = false;
            // Validate variant URLs
            $('#r3dfpv-variants-container').find('input[type="url"]').each(function(){
                var val = $(this).val().trim();
                if (val.length === 0) {
                    return; // skip empty
                }
                try {
                    var u = new URL(val);
                    if (u.protocol !== 'https:') {
                        invalid = true;
                    }
                    // simple extension check
                    if (!u.pathname.toLowerCase().endsWith('.stl')) {
                        invalid = true;
                    }
                } catch (err) {
                    invalid = true;
                }
            });

            if (invalid) {
                alert(r3dfpv_product_fields_i18n.invalid_url);
                e.preventDefault();
                return false;
            }

            // Validate prices
            $('#r3dfpv-variants-container').find('input[type="number"]').each(function(){
                var v = $(this).val();
                if (v === '') {
                    return; // empty means inherit main price
                }
                var n = parseFloat(v);
                if (isNaN(n) || n < 0) {
                    alert(r3dfpv_product_fields_i18n.invalid_price);
                    invalid = true;
                    e.preventDefault();
                    return false;
                }
            });

            if (invalid) {
                return false;
            }

            return true;
        });

    });
})(jQuery);
