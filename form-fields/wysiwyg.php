<?php
/**
 * Elementor Forms – WYSIWYG (TinyMCE) field type
 *
 * Drop this file into:  /wp-content/plugins/elementor-form-wysiwyg-field/form-fields/wysiwyg.php
 *
 * Requires: Elementor Pro 3.22+  |  WordPress 6.1+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ElementorPro\Modules\Forms\Fields\Field_Base;

class EFWF_Wysiwyg_Field extends Field_Base {

	/* -------------------------------------------------------------------------
	 *  Basics
	 * ---------------------------------------------------------------------- */

	public function get_type(): string { return 'wysiwyg'; }

	public function get_name(): string {
		return esc_html__( 'WYSIWYG', 'elementor-form-wysiwyg-field' );
	}

	/* -------------------------------------------------------------------------
	 *  Front-end render
	 * ---------------------------------------------------------------------- */

	public function render( $item, $index, $form ) {
		wp_enqueue_editor();                                 // load WP’s TinyMCE bundle

		$field_id = 'efwf_' . $form->get_id() . '_' . $index; // canonical id

		$form->add_render_attribute( 'textarea' . $index, [
			'class' => 'elementor-field elementor-wysiwyg',
			'id'    => $field_id,
			'rows'  => 8,
		] );

		echo '<textarea ' .
		     $form->get_render_attribute_string( 'textarea' . $index ) .
		     '></textarea>';
	}

	/* -------------------------------------------------------------------------
	 *  Sanitisation
	 * ---------------------------------------------------------------------- */

	public function sanitize_field( $value, $field ) {
		return wp_kses_post( $field['raw_value'] );          // keep safe HTML only
	}

	/* -------------------------------------------------------------------------
	 *  Bootstrapping
	 * ---------------------------------------------------------------------- */

	public function __construct() {
		parent::__construct();

		// 1. Load TinyMCE assets on front-end + inside builder preview
		add_action( 'wp_enqueue_scripts',     [ $this, 'enqueue_editor_assets' ] );
		add_action( 'elementor/preview/init', [ $this, 'enqueue_editor_assets' ] );

		// 2. Initialise editors (front-end)
		add_action( 'wp_footer',              [ $this, 'inline_editor_bootstrap' ], 20 );

		// 3. Provide builder template + live re-init logic
		add_action( 'elementor/preview/init', [ $this, 'setup_editor_template' ] );
	}

	public function enqueue_editor_assets(): void {
		if ( user_can_richedit() && function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
		}
	}

	/* -------------------------------------------------------------------------
	 *  Front-end TinyMCE init
	 * ---------------------------------------------------------------------- */

	public function inline_editor_bootstrap(): void { ?>
		<script>
			document.addEventListener( 'DOMContentLoaded', () => {
				if ( ! window.tinymce ) { return; }

				const settings = {
					selector : 'textarea.elementor-wysiwyg',
					menubar  : false,
					branding : false,
					toolbar  : 'formatselect | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat',
					setup    : ed => ed.on( 'change', () => ed.save() ),
				};

				/* remove any ghost editors (eg. from AJAX refresh) */
				Array.from( tinymce.editors )
				     .filter( ed => ed.targetElm && ed.targetElm.classList.contains( 'elementor-wysiwyg' ) )
				     .forEach( ed => ed.remove() );

				tinymce.init( settings );
			} );
		</script>
	<?php }

	/* -------------------------------------------------------------------------
	 *  Builder-side template + live re-initialisation
	 * ---------------------------------------------------------------------- */

	public function setup_editor_template(): void {
		add_action( 'wp_footer', function () { ?>
			<script>
				jQuery( function ( $ ) {

					/* ------------ helper: (re)initialise any missing editors ---------- */
					const initEditors = scope => {
						if ( ! window.tinymce ) { return; }
						$( scope ).find( 'textarea.elementor-wysiwyg' ).each( function () {
							const id = this.id;
							if ( ! id || tinymce.get( id ) ) { return; }          // skip if active
							tinymce.init( {
								target   : this,
								menubar  : false,
								branding : false,
								toolbar  : 'formatselect | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat',
								setup    : ed => ed.on( 'change', () => ed.save() ),
							} );
						} );
					};

					/* ------------ inject field template into the preview -------------- */
					elementor.hooks.addFilter(
						'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
						( _html, item, i, settings ) => {
							const formId  = settings.form_id || 'preview';
							const fieldId = `efwf_${ formId }_${ i }`;
							const classes = `elementor-field elementor-wysiwyg ${ item.css_classes }`;
							return `<textarea id="${ fieldId }" class="${ classes }" rows="8"></textarea>`;
						},
						10, 4
					);

					/* ------------ first paint in the preview iframe ------------------- */
					elementorFrontend.hooks.addAction(
						'frontend/element_ready/form.default',
						widget => initEditors( widget[0] )
					);

					/* ------------ every control tweak (label, required, etc.) --------- */
					elementor.channels.editor.on( 'element:after:update', model => {
						if ( model.get( 'widgetType' ) === 'form' ) {
							initEditors( elementor.$preview[0] );
						}
					} );
				} );
			</script>
		<?php } );
	}

	/**
	 * Provide builder template + bullet-proof re-init logic.
	 */
	public function setup_editor_template(): void {
	
		add_action( 'wp_footer', function () { ?>
			<script>
				jQuery( function ( $ ) {
	
					/* -------- helper: (re)initialise missing editors ----------------- */
					const initEditors = root => {
						if ( ! window.tinymce ) { return; }
	
						$( root ).find( 'textarea.elementor-wysiwyg' ).each( function () {
							const id = this.id;
							if ( ! id || tinymce.get( id ) ) { return; }      // skip if already active
	
							tinymce.init( {
								target   : this,
								menubar  : false,
								branding : false,
								toolbar  : 'formatselect | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat',
								setup    : ed => ed.on( 'change', () => ed.save() ),
							} );
						} );
					};
	
					/* -------- inject the field template so Elementor shows it -------- */
					elementor.hooks.addFilter(
						'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
						( _html, item, i, settings ) => {
							const formId  = settings.form_id || 'preview';
							const fieldId = `efwf_${ formId }_${ i }`;
							const classes = `elementor-field elementor-wysiwyg ${ item.css_classes }`;
							return `<textarea id="${ fieldId }" class="${ classes }" rows="8"></textarea>`;
						},
						10, 4
					);
	
					/* -------- first paint when the widget appears in the preview ----- */
					elementorFrontend.hooks.addAction(
						'frontend/element_ready/form.default',
						widget => initEditors( widget[0] )
					);
	
					/* -------- watch the entire preview iframe for DOM swaps ---------- */
					const previewRoot = elementor.$preview[0];
					const observer    = new MutationObserver( mutations => {
						for ( const m of mutations ) {
							if ( m.addedNodes.length || m.removedNodes.length ) {
								initEditors( previewRoot );
								break;  // one re-scan is enough
							}
						}
					});
	
					observer.observe( previewRoot, { childList: true, subtree: true } );
				} );
			</script>
		<?php } );
	}

}
