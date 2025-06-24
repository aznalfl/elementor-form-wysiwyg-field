<?php
/**
 * Elementor Forms – WYSIWYG (TinyMCE) field type
 * Tested up to Elementor Pro 3.25 / WordPress 6.5 (June 2025)
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

use ElementorPro\Modules\Forms\Fields\Field_Base;

class EFWF_Wysiwyg_Field extends Field_Base {

	public function get_type(): string { return 'wysiwyg'; }
	public function get_name(): string { return esc_html__( 'WYSIWYG', 'elementor-form-wysiwyg-field' ); }

	public function render( $item, $index, $form ) {
		if ( function_exists( 'wp_enqueue_editor' ) ) { wp_enqueue_editor(); }

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

	public function sanitize_field( $value, $field ) { return wp_kses_post( $field['raw_value'] ); }

	public function __construct() {
		parent::__construct();
		add_action( 'wp_enqueue_scripts',     [ $this, 'enqueue_editor_assets' ] );
		add_action( 'elementor/preview/init', [ $this, 'enqueue_editor_assets' ] );
		add_action( 'wp_footer',              [ $this, 'frontend_editor_bootstrap' ], 20 );
		add_action( 'elementor/preview/init', [ $this, 'builder_editor_template' ] );
	}

	public function enqueue_editor_assets(): void {
		if ( user_can_richedit() && function_exists( 'wp_enqueue_editor' ) ) { wp_enqueue_editor(); }
	}

	/* ---------- public-page TinyMCE init -------------------------------- */
	public function frontend_editor_bootstrap(): void { ?>
		<script>
		document.addEventListener( 'DOMContentLoaded', () => {
			if ( ! window.tinymce ) { return; }

			tinymce.editors
				.filter( ed => ed.targetElm && ed.targetElm.classList.contains( 'elementor-wysiwyg' ) )
				.forEach( ed => ed.remove() );

			tinymce.init( {
				selector : 'textarea.elementor-wysiwyg',
+				plugins  : 'lists',
				menubar  : false,
				branding : false,
				toolbar  : 'formatselect | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat',
				setup    : ed => ed.on( 'change', () => ed.save() ),
			} );
		} );
		</script>
	<?php }

	/* ---------- builder preview template + live re-init ---------------- */
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
+							plugins  : 'lists',
							menubar  : false,
							branding : false,
							toolbar  : 'formatselect | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat',
							setup    : ed => ed.on( 'change', () => ed.save() ),
						} );
					} );
				}

				elementor.hooks.addFilter(
					'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
					( _html, item, i, settings ) => {
						const formId  = settings.form_id || 'preview';
						const fieldId = 'efwf_' + formId + '_' + i;
						const classes = 'elementor-field elementor-wysiwyg ' + item.css_classes;
						return '<textarea id="' + fieldId + '" class="' + classes + '" rows="8"></textarea>';
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
}
