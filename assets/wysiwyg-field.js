(function($){
    // Initialise TinyMCE on WYSIWYG fields within a given scope
    const initWysiwyg = function($scope) {
        $scope.find('.elementor-wysiwyg-field').each(function(){
            const id = $(this).attr('id');
            // Skip if already initialised
            if (window.tinymce?.get(id)) {
                return;
            }
            tinymce.init({
                selector: '#' + id,
                menubar: false,
                toolbar: 'bold italic underline | bullist numlist | link',
                plugins: 'lists link',
                skin: 'oxide',
                content_css: 'default'
            });
        });
    };

    // Wait until Elementor front-end is ready
    $(window).on('elementor/frontend/init', function(){
        // Initialise on initial page load
        initWysiwyg($(document));

        // Initialise on every Form widget render (covers AJAX-loaded forms)
        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.hooks.addAction('frontend/element_ready/form.default', initWysiwyg);
        }
    });
})(jQuery);