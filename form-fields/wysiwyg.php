<?php
namespace EFS;

use ElementorPro\Modules\Forms\Fields\Field_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wysiwyg_Field extends Field_Base {

    public function __construct() {
        parent::__construct();
        // Hook into the editor preview so we actually see a placeholder
        add_action( 'elementor/preview/init', [ $this, 'editor_preview_init' ] );
    }

    public function get_type(): string {
        return 'wysiwyg';
    }

    public function get_name(): string {
        return __( 'WYSIWYG', 'efs-wysiwyg' );
    }

    /**
     * Enqueue TinyMCE only when the form appears.
     */
    public function get_script_depends(): array {
        // Load WP’s built-in editor assets (TinyMCE + glue) and jQuery
        wp_enqueue_editor();
        // Register our init stub
        wp_register_script(
            'efs-wysiwyg',
            plugins_url( '../assets/wysiwyg-field.js', __FILE__ ),
            [ 'jquery', 'tinymce', 'elementor-frontend' ],
            '1.0.0',
            true
        );
        return [ 'efs-wysiwyg' ];
    }

    /**
     * Render the textarea that TinyMCE will upgrade.
     */
    public function render( $item, $item_index, $form ): void {
        $field_id = 'form-field-' . $item_index;
        $form->add_render_attribute( $field_id, [
            'id'       => $field_id,
            'name'     => $item['field_name'],
            'class'    => 'elementor-field elementor-wysiwyg-field',
            'rows'     => '10',
            'required' => $item['required'] ? 'required' : false,
        ] );
        echo '<textarea ' . $form->get_render_attribute_string( $field_id ) . '></textarea>';
    }

    /**
     * Sanitise submitted HTML.
     */
    public function validation( $field, $record, $ajax_handler ): void {
        $field['value'] = wp_kses_post( $field['value'] );
    }

    /**
     * Set up an editor‐preview template so the WYSIWYG field
     * shows in the Elementor canvas.
     */
    public function editor_preview_init() {
        add_action( 'wp_footer', [ $this, 'content_template_script' ] );
    }

    public function content_template_script() {
        ?>
        <script>
        jQuery( document ).ready( () => {
            elementor.hooks.addFilter(
                'elementor_pro/forms/content_template/field/wysiwyg',
                ( inputField, item, i ) => {
                    const fieldId  = `form-field-${i}`;
                    const classes  = `elementor-field elementor-wysiwyg-field ${ item.css_classes }`;
                    return `<textarea id="${fieldId}" class="${classes}" rows="8" placeholder="WYSIWYG content…"></textarea>`;
                },
                10, 3
            );
        });
        </script>
        <?php
    }
}