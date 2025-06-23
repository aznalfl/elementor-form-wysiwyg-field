<?php
namespace EFS;

use ElementorPro\Modules\Forms\Fields\Field_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wysiwyg_Field extends Field_Base {

    // ───── Basic info ─────
    public function get_type(): string {
        return 'wysiwyg';
    }
    public function get_name(): string {
        return __( 'WYSIWYG', 'efs-wysiwyg' );
    }

    // ───── Enqueue scripts on front-end ─────
    public function get_script_depends(): array {
        // TinyMCE core is loaded by wp_enqueue_editor via the main plugin file
        return [ 'efs-wysiwyg' ];
    }

    // ───── Render on live pages ─────
    public function render( $item, $item_index, $form ): void {
        $field_id   = 'form-field-' . $item_index;
        $field_name = $item['field_name'] ?? 'wysiwyg_' . $item_index;
        $form->add_render_attribute(
            $field_id,
            [
                'id'       => $field_id,
                'name'     => $field_name,
                'class'    => 'elementor-field elementor-wysiwyg-field',
                'rows'     => 8,
                'required' => ! empty( $item['required'] ) ? 'required' : false,
            ]
        );
        echo '<textarea ' . $form->get_render_attribute_string( $field_id ) . '></textarea>';
    }

    // ───── Sanitise submission ─────
    public function validation( $field, $record, $ajax_handler ): void {
        $field['value'] = wp_kses_post( $field['value'] );
    }

    // ───── Editor preview template ─────
    public function __construct() {
        parent::__construct();
        add_action( 'elementor/preview/init', [ $this, 'editor_preview_footer' ] );
    }
    public function editor_preview_footer(): void {
        add_action( 'wp_footer', [ $this, 'preview_template_script' ] );
    }
    public function preview_template_script(): void {
        ?>
        <script>
        jQuery( document ).ready( () => {
            elementor.hooks.addFilter(
                'elementor_pro/forms/content_template/field/wysiwyg',
                ( inputField, item, i ) => {
                    const id      = `form-field-${ i }`;
                    const classes = `elementor-field elementor-wysiwyg-field ${ item.css_classes }`;

                    // Wait for WP.editor then init TinyMCE
                    const tryInit = () => {
                        if ( window.wp && wp.editor && typeof wp.editor.initialize === 'function' ) {
                            wp.editor.initialize( id, {
                                tinymce: {
                                    toolbar1: 'bold italic underline | bullist numlist | link',
                                    plugins: 'lists link',
                                    menubar: false,
                                },
                                quicktags: false,
                            } );
                        } else {
                            setTimeout( tryInit, 200 );
                        }
                    };
                    setTimeout( tryInit, 0 );

                    return `<textarea id="${ id }" class="${ classes }" rows="8" placeholder="WYSIWYG content…"></textarea>`;
                },
                10, 3
            );
        });
        </script>
        <?php
    }
}