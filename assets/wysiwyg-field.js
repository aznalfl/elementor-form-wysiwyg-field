(function ($) {
    // Wait until Elementor front‑end scripts are ready.
    $(window).on('elementor/frontend/init', function () {
        $('.elementor-wysiwyg-field').each(function () {
            const id = $(this).attr('id');
            if (window.tinymce?.get(id)) {
                return; // Already initialised.
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
    });
})(jQuery);