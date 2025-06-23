(function ($) {

    /* --------------------------------------------------------------------
     * 1. Live-preview template for Elementor editor
     * ------------------------------------------------------------------ */
    if (typeof elementor !== 'undefined') {
        elementor.hooks.addFilter(
            'elementor_pro/forms/content_template/field/wysiwyg',
            function (inputField, item, i) {
                const fieldId = `form-field-${i}`;
                const classes = `elementor-field elementor-wysiwyg-field ${item.css_classes}`;
                return `<textarea id="${fieldId}" class="${classes}" rows="8" placeholder="WYSIWYG content…"></textarea>`;
            },
            10,
            3
        );
    }

    /* --------------------------------------------------------------------
     * 2. TinyMCE initialiser (front-end & editor preview)
     * ------------------------------------------------------------------ */
    const initWysiwyg = function ($scope) {
        $scope.find('.elementor-wysiwyg-field').each(function () {
            const id = $(this).attr('id');
            // Skip if TinyMCE already attached
            if (window.tinymce?.get(id)) {
                return;
            }
            tinymce.init({
                selector: '#' + id,
                menubar: false,
                toolbar: 'bold italic underline | bullist numlist | link',
                plugins: 'lists link',
                skin: 'oxide',
                content_css: 'default',
            });
        });
    };

    /* --------------------------------------------------------------------
     * 3. Bind to Elementor events
     * ------------------------------------------------------------------ */
    $(window).on('elementor/frontend/init', function () {

        // Initial page load
        initWysiwyg($(document));

        // Handle forms loaded dynamically (pop-ups, tabs, etc.)
        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.hooks.addAction(
                'frontend/element_ready/form.default',
                initWysiwyg
            );
        }
    });

})(jQuery);