<?php
/**
 * Elementor Forms – WYSIWYG (TinyMCE) field type
 * Part of the “Elementor Forms WYSIWYG Field” add-on.
 * Tested up to Elementor Pro 3.25 / WordPress 6.5 (June 2025).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

use ElementorPro\Modules\Forms\Fields\Field_Base;

class EFWF_Wysiwyg_Field extends Field_Base {

	/* ------------------------------------------------------------------ */
	public function get_type(): string { return 'wysiwyg'; }

	public function get_name(): string {
		return esc_html__( 'WYSIWYG', 'elementor-form-wysiwyg-field' );
	}

	/**
	 * Tweak TinyMCE toolbar button hover / focus colours.
	 * Runs late in the footer so it overrides the default Oxide skin.
	 */
    public function inject_toolbar_css(): void { ?>
    	<style>
    		/* TinyMCE 4 toolbar loaded by this field (front-end + builder preview) */
    		.elementor .mce-tinymce button:hover,
    		.elementor .mce-tinymce button:focus {
    			background-color:#e5e5e5 !important;   /* soft grey bg */
    			color:#000 !important;                 /* sets glyph colour in modern skins */
    		}
    
    		/* icon glyphs (WP skin) */
    		.elementor .mce-tinymce button:hover i,
    		.elementor .mce-tinymce button:focus i {
    			color:#000 !important;                 /* fallback for older icon sets */
    		}
    
    		/* If SVG icons are used */
    		.elementor .mce-tinymce button:hover svg,
    		.elementor .mce-tinymce button:focus svg {
    			fill:currentColor !important;
    		}
    	</style>
    <?php }
	

	/* ------------------------------------------------------------------ */
	public function render( $item, $index, $form ) {
		if ( function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();                      // load WP TinyMCE bundle
		}

		$field_id = 'efwf_' . $form->get_id() . '_' . $index;

		$form->add_render_attribute( 'textarea' . $index, [
			'class' => 'elementor-field elementor-wysiwyg',
			'id'    => $field_id,
			'rows'  => 8,
		] );

		echo '<textarea ' .
		     $form->get_render_attribute_string( 'textarea' . $index ) .
		     '></textarea>';
	}

	/* ------------------------------------------------------------------ */
	public function sanitize_field( $value, $field ) {
		return wp_kses_post( $field['raw_value'] );
	}

	/* ------------------------------------------------------------------ */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_enqueue_scripts',     [ $this, 'enqueue_editor_assets' ] );
		add_action( 'elementor/preview/init', [ $this, 'enqueue_editor_assets' ] );

		add_action( 'wp_footer',              [ $this, 'frontend_editor_bootstrap' ], 20 );
		add_action( 'elementor/preview/init', [ $this, 'builder_editor_template' ] );

		// Front-end + builder preview: inject custom toolbar CSS
		add_action( 'wp_footer', [ $this, 'inject_toolbar_css' ], 99 );
	}

	public function enqueue_editor_assets(): void {
		if ( user_can_richedit() && function_exists( 'wp_enqueue_editor' ) ) {
			wp_enqueue_editor();
		}
	}

	/* ---------- TinyMCE on the PUBLIC page --------------------------- */
	public function frontend_editor_bootstrap(): void { ?>
		<script>
		document.addEventListener( 'DOMContentLoaded', () => {
			if ( ! window.tinymce ) { return; }

			/* remove orphaned editors then init */
			tinymce.editors
				.filter( ed => ed.targetElm && ed.targetElm.classList.contains( 'elementor-wysiwyg' ) )
				.forEach( ed => ed.remove() );

			tinymce.init( {
				selector : 'textarea.elementor-wysiwyg',
				plugins  : ['lists'],                                      // ← added
				menubar  : false,
				branding : false,
				toolbar  : 'formatselect | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat',
				setup    : ed => ed.on( 'change', () => ed.save() ),
			} );
		} );
		</script>
	<?php }

	/* ---------- Builder preview template & live re-init -------------- */
	public function builder_editor_template(): void {
		add_action( 'wp_footer', function () { ?>
			<script>
			jQuery( function ( $ ) {

				function initEditors( root ) {
					if ( ! window.tinymce ) { return; }

					$( root ).find( 'textarea.elementor-wysiwyg' ).each( function () {
						const id = this.id;
						if ( ! id ) { return; }

						const existing = tinymce.get( id );
						if ( existing ) {
							if ( existing.targetElm && document.contains( existing.targetElm ) ) { return; }
							existing.remove();
						}

						tinymce.init( {
							target   : this,
							plugins  : ['lists'],                          // ← added
							menubar  : false,
							branding : false,
							toolbar  : 'formatselect | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat',
							setup    : ed => ed.on( 'change', () => ed.save() ),
						} );
					} );
				}

				/* template injection */
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

				const previewRoot = elementor.$preview[0];
				if ( previewRoot ) {
					new MutationObserver( mList => {
						for ( const m of mList ) {
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

	add_filter(
		'elementor_pro/forms/render/item/wysiwyg',
		function( $item ) {
			unset( $item['attributes']['pattern'] ); // strip any leftover pattern
			return $item;
		},
		9
	);

}
