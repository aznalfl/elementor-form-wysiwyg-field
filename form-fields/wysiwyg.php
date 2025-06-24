<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ElementorPro\Modules\Forms\Fields\Field_Base;

/**
 * Elementor Forms – WYSIWYG field.
 */
class EFWF_Wysiwyg_Field extends Field_Base {

	/* ========== Basics ========== */

	public function get_type(): string {
		return 'wysiwyg';
	}

	public function get_name(): string {
		return esc_html__( 'WYSIWYG', 'elementor-form-wysiwyg-field' );
	}

	/* ========== Front-end render ========== */

	public function render( $item, $index, $form ): void {

		$form->add_render_attribute(
			'textarea' . $index,
			[
				'class' => 'elementor-field elementor-wysiwyg', // target for TinyMCE
				'rows'  => 8,
			]
		);

		echo '<textarea ' .               // phpcs:ignore WordPress.Security.EscapeOutput
		     $form->get_render_attribute_string( 'textarea' . $index ) .
		     '></textarea>';
	}

	/* ========== Sanitisation ========== */

	public function sanitize_field( $value, $field ) {
		// Keep safe HTML; adjust allowed tags if you need tables, iframes, etc.
		return wp_kses_post( $field['raw_value'] );
	}

	/* ========== Scripts ========== */

	public function __construct() {
		parent::__construct();

		// Load TinyMCE & friends on the pages where the field appears.
		add_action( 'wp_enqueue_scripts',          [ $this, 'enqueue_editor_assets' ] );
		add_action( 'elementor/preview/init',      [ $this, 'enqueue_editor_assets' ] );

		// Initialise editors.
		add_action( 'wp_footer',                   [ $this, 'inline_editor_bootstrap' ], 20 );
		add_action( 'elementor/preview/init',      [ $this, 'setup_editor_template' ] );
	}

	/**
	 * Ask WordPress to enqueue its TinyMCE bundle.
	 * Works both on the front end and inside the Elementor editor iframe.
	 */
	public function enqueue_editor_assets(): void {
		if ( user_can_richedit() && function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor(); // Core will enqueue tinymce & quicktags.
		}
	}

	/**
	 * Initialise TinyMCE on any textarea with class `.elementor-wysiwyg`.
	 * Runs after all scripts/styles are printed.
	 */
	public function inline_editor_bootstrap(): void { ?>
		<script>
			document.addEventListener( 'DOMContentLoaded', () => {
				if ( window.tinymce ) {
					const settings = {
						selector: 'textarea.elementor-wysiwyg',
						menubar: false,
						branding: false,
						toolbar: 'formatselect | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat',
						setup: editor => {
							editor.on( 'change', () => editor.save() ); // keep <textarea> in sync
						},
					};
					// Remove any auto-initialised instance (e.g. after AJAX refresh) then re-init
					Array.from( tinymce.editors )
						.filter( ed => ed.targetElm && ed.targetElm.classList.contains( 'elementor-wysiwyg' ) )
						.forEach( ed => ed.remove() );
					tinymce.init( settings );
				}
			} );
		</script>
	<?php }

	/**
	 * Provide a content-template so the field displays as a rich editor
	 * inside the Elementor panel/preview while building the form.
	 */
	public function setup_editor_template(): void {
		add_action( 'wp_footer', function () { ?>
			<script>
				jQuery( function ( $ ) {

					/* Insert the field into the live preview ------------------ */
					elementor.hooks.addFilter(
						'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
						( inputHtml, item, i ) => {
							const fieldId   = `form_field_${ i }`;
							const cssClass  = `elementor-field elementor-wysiwyg ${ item.css_classes }`;
							return `<textarea id="${ fieldId }" rows="8" class="${ cssClass }"></textarea>`;
						},
						10, 3
					);

					/* Initialise TinyMCE inside the preview iframe ------------ */
					elementorFrontend.hooks.addAction(
						'frontend/element_ready/form.default',
						() => {
							if ( window.tinymce ) {
								setTimeout( () => {
									window.tinymce.init( {
										selector: 'textarea.elementor-wysiwyg',
										menubar: false,
										branding: false,
										toolbar: 'formatselect | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat',
										setup: ed => {
											ed.on( 'change', () => ed.save() );
										},
									} );
								}, 100 );
							}
						}
					);
				} );
			</script>
		<?php } );
	}
}
