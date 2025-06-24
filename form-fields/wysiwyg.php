<?php
namespace EFS;

use ElementorPro\Modules\Forms\Fields\Field_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wysiwyg_Field extends Field_Base {

	/* ─── basic meta ─── */
	public function get_type(): string { return 'wysiwyg'; }
	public function get_name(): string { return __( 'WYSIWYG', 'efs-wysiwyg' ); }

	/* ─── assets ─── */
	public $depended_scripts = [ 'tinymce-cdn' ];          // front-end
	public function get_script_depends(): array { return [ 'tinymce-cdn' ]; }

	/* ─── front-end render ─── */
	public function render( $item, $index, $form ): void {
		$form->add_render_attribute( "input$index", [
			'type'  => 'wysiwyg',                             // <-- unique selector
			'id'    => "form_field_$index",
			'class' => 'elementor-field elementor-wysiwyg',
			'rows'  => 8,
		] );
		echo '<textarea ' . $form->get_render_attribute_string( "input$index" ) . '></textarea>';
	}

	/* ─── sanitise submission ─── */
	public function validation( $field, $record, $ajax ): void {
		$field['value'] = wp_kses_post( $field['value'] );
	}

	/* ─── constructor: inject inline JS for both editor & front-end ─── */
	public function __construct() {
		parent::__construct();

		// load TinyMCE & init on live pages
		add_action( 'wp_footer',               [ $this, 'inline_script_front' ] );
		// load TinyMCE & init inside the editor preview iframe
		add_action( 'elementor/preview/init',  [ $this, 'inline_script_editor' ] );
	}

	/* front-end initialiser */
	public function inline_script_front() { ?>
		<script>
		(function ($) {
			const boot = () => {
				if ( window.tinymce ) {
					tinymce.init({
						selector: 'textarea[type="wysiwyg"]',
						toolbar:  'bold italic underline | bullist numlist | link',
						plugins:  'lists link',
						menubar:  false,
						setup(ed){ ed.on('change', ()=>ed.save()); }
					});
				} else { setTimeout(boot, 300); }
			};
			boot();
		})(jQuery);
		</script><?php
	}

	/* editor-preview initialiser + HTML template */
	public function inline_script_editor() { ?>
		<script>
		jQuery( document ).ready( () => {

			const initMce = () => {
				if ( window.tinymce ) {
					tinymce.remove('textarea[type="wysiwyg"]'); // avoid duplicates
					tinymce.init({
						selector: 'textarea[type="wysiwyg"]',
						toolbar:  'bold italic underline | bullist numlist | link',
						plugins:  'lists link',
						menubar:  false,
						setup(ed){ ed.on('change', ()=>ed.save()); }
					});
				} else { setTimeout(initMce, 300); }
			};

			/* inject the textarea into the canvas */
			elementor.hooks.addFilter(
				'elementor_pro/forms/content_template/field/wysiwyg',
				( input, item, i ) => {
					const req = item.required ? 'required' : '';
					return `<textarea type="wysiwyg" id="form_field_${i}" class="elementor-field elementor-wysiwyg ${item.css_classes}" rows="8" ${req}></textarea>`;
				}, 10, 3
			);

			/* re-initialise TinyMCE whenever a form loads */
			elementorFrontend.hooks.addAction( 'frontend/element_ready/form.default', initMce );
		});
		</script><?php
	}
}