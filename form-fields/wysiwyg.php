<?php
/**
 * Elementor Forms – WYSIWYG (TinyMCE) field type  
 * Part of the “Elementor Forms WYSIWYG Field” add-on.  
 * Tested up to Elementor Pro 3.25 / WordPress 6.5 (June 2025).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

use ElementorPro\Modules\Forms\Fields\Field_Base;

class EFWF_Wysiwyg_Field extends Field_Base {

	/* ---------------------------------------------------------------------
	 * Meta
	 * ------------------------------------------------------------------ */
	public function get_type() : string { return 'wysiwyg'; }

	public function get_name() : string {
		return esc_html__( 'WYSIWYG', 'elementor-form-wysiwyg-field' );
	}

	/* ---------------------------------------------------------------------
	 * Toolbar colour tweaks
	 * ------------------------------------------------------------------ */
	public function inject_toolbar_css() : void { ?>
		<style>
		.elementor .mce-tinymce button:hover,
		.elementor .mce-tinymce button:focus {
			background:#e5e5e5!important;
			color:#000!important;
		}
		.elementor .mce-tinymce button:hover i,
		.elementor .mce-tinymce button:focus i,
		.elementor .mce-tinymce button:hover svg,
		.elementor .mce-tinymce button:focus svg {
			color:currentColor!important;
			fill:currentColor!important;
		}
		</style>
	<?php }

	/* ---------------------------------------------------------------------
	 * Field markup
	 * ------------------------------------------------------------------ */
	public function render( $item, $index, $form ) {
		if ( function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();                       // core TinyMCE bundle
		}

		$field_id = 'efwf_' . $form->get_id() . '_' . $index;

		$form->add_render_attribute(
			'textarea' . $index,
			[
				'class' => 'elementor-field elementor-wysiwyg',
				'id'    => $field_id,
				'name'  => $item['custom_id'],        // crucial ⇦⇦
				'rows'  => 8,
			]
		);

		echo '<textarea ' .
		     $form->get_render_attribute_string( 'textarea' . $index ) .
		     '></textarea>';
	}

	/* Store exactly what the user typed (we’ll trust wp_kses later) */
	public function sanitize_field( $value, $field ) {
		return wp_kses_post( $field['raw_value'] );
	}

	/* ---------------------------------------------------------------------
	 * Bootstrap
	 * ------------------------------------------------------------------ */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_enqueue_scripts',     [ $this, 'enqueue_editor_assets' ] );
		add_action( 'elementor/preview/init', [ $this, 'enqueue_editor_assets' ] );

		add_action( 'wp_footer',              [ $this, 'frontend_editor_bootstrap' ], 20 );
		add_action( 'elementor/preview/init', [ $this, 'builder_editor_template' ] );

		add_action( 'wp_footer',              [ $this, 'inject_toolbar_css' ], 99 );
	}

	public function enqueue_editor_assets() : void {
		if ( user_can_richedit() && function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
		}
	}

	/* ---------------------------------------------------------------------
	 * Front-end (public page)
	 * ------------------------------------------------------------------ */
	public function frontend_editor_bootstrap() : void { ?>
		<script>
		document.addEventListener( 'DOMContentLoaded', () => {

			if ( ! window.tinymce ) { return; }

			/* Remove orphaned instances then init a fresh one */
			tinymce.editors
				.filter( ed => ed.targetElm &&
				               ed.targetElm.classList.contains( 'elementor-wysiwyg' ) )
				.forEach( ed => ed.remove() );

			tinymce.init( {
				selector : 'textarea.elementor-wysiwyg',
				plugins  : [ 'lists' ],
				menubar  : false,
				branding : false,
				toolbar  : 'formatselect | bold italic underline | bullist numlist |' +
				           ' alignleft aligncenter alignright | link removeformat',
				setup    : ed => ed.on( 'change', () => ed.save() ),
			} );

			/* 🔑 NEW: make absolutely sure the editor writes its content back
			 *          into the hidden <textarea> *before* Elementor serialises
			 *          and submits the form.
			 */
			document.addEventListener(
				'submit',
				() => { if ( window.tinymce ) { tinymce.triggerSave(); } },
				true   // capture phase – run before Elementor’s own handler
			);

		} );
		</script>
	<?php }

	/* ---------------------------------------------------------------------
	 * Builder preview (Elementor editor)
	 * ------------------------------------------------------------------ */
	public function builder_editor_template() : void {
		add_action( 'wp_footer', function () { ?>
			<script>
			jQuery( $ => {

				function initEditors( root ) {
					if ( ! window.tinymce ) { return; }

					$( root ).find( 'textarea.elementor-wysiwyg' ).each( function () {
						const id = this.id;
						if ( ! id ) { return; }

						const existing = tinymce.get( id );
						if ( existing ) {
							if ( existing.targetElm && document.contains( existing.targetElm ) ) {
								return;          // already active inside live node
							}
							existing.remove();  // remove from detached template
						}

						tinymce.init( {
							target   : this,
							plugins  : [ 'lists' ],
							menubar  : false,
							branding : false,
							toolbar  : 'formatselect | bold italic underline | bullist numlist |' +
							           ' alignleft aligncenter alignright | link removeformat',
							setup    : ed => ed.on( 'change', () => ed.save() ),
						} );
					} );
				}

				/* Inject template HTML into the panel preview */
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

				elementorFrontend.hooks.addAction(
					'frontend/element_ready/form.default',
					widget => initEditors( widget[0] )
				);

				/* Watch for template changes */
				const previewRoot = elementor.$preview[0];
				if ( previewRoot ) {
					new MutationObserver( mlist => {
						for ( const m of mlist ) {
							if ( m.addedNodes.length || m.removedNodes.length ) {
								initEditors( previewRoot );
								break;
							}
						}
					} ).observe( previewRoot, { childList: true, subtree: true } );
				}
			} );
			</script>
		<?php } );
	}
}

/* -----------------------------------------------------------------------
 * Filters: remove pattern & force validation pass
 * -------------------------------------------------------------------- */
add_filter(
	'elementor_pro/forms/render/item/wysiwyg',
	static function ( $item ) {
		unset( $item['attributes']['pattern'] );   // Elementor adds it by default
		return $item;
	},
	9
);

add_filter(
	'elementor_pro/forms/validation/item/wysiwyg',
	static function ( $result, $field, $item ) {
		$result['passed'] = true;                  // we sanitise later anyway
		return $result;
	},
	10,
	3
);
