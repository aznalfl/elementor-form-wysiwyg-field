(function($){

  // Front-end & dynamic-load TinyMCE initialiser via WP.editor API fallback
  const initWysiwyg = ($scope) => {
    $scope.find('.elementor-wysiwyg-field').each(function(){
      const id = $(this).attr('id');
      if ( window.tinymce?.get(id) ) return;

      if ( window.wp?.editor?.initialize ) {
        wp.editor.initialize(id, {
          tinymce: {
            toolbar1: 'bold italic underline | bullist numlist | link',
            plugins: 'lists link',
            menubar: false,
          },
          quicktags: false,
        });
      } else if ( window.tinymce?.init ) {
        tinymce.init({
          selector: '#' + id,
          menubar: false,
          toolbar: 'bold italic underline | bullist numlist | link',
          plugins: 'lists link',
        });
      }
    });
  };

  // Tie into Elementor front-end events
  $(window).on('elementor/frontend/init', () => {
    initWysiwyg($(document));
    if ( window.elementorFrontend ) {
      elementorFrontend.hooks.addAction('frontend/element_ready/form.default', initWysiwyg);
    }
  });

})(jQuery);