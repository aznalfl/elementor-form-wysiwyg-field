<?php
namespace EFS;

use ElementorPro\Modules\Forms\Fields\Field_Base;

if ( ! defined( 'ABSPATH' ) ) exit;

class Wysiwyg_Field extends Field_Base {

    public function __construct() {
        parent::__construct();
        // No need for wp_footer hacks any more
    }

    public function get_type(): string {
        return 'wysiwyg';
    }

    public function get_name(): string {
        return __( 'WYSIWYG', 'efs-wysiwyg' );
    }

    public function get_script_depends(): array {
        wp_enqueue_editor();
        wp_register_script(
            'efs-wysiwyg',
            plugins_url( '../assets/wysiwyg-field.js', __FILE__ ),
            [ 'jquery', 'tinymce', 'elementor-frontend' ],
            '1.0.0',
            true
        );
        return [ 'efs-wysiwyg' ];
    }

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

    public function validation( $field, $record, $ajax_handler ): void {
        $field['value'] = wp_kses_post( $field['value'] );
    }

    /**
     * Provide a live preview template in the Elementor editor.
     */
    public function content_template() {
        ?>
        <textarea id="form-field-{{ data._sub_fields_index }}" 
                  class="elementor-field elementor-wysiwyg-field {{ data.css_classes }}" 
                  rows="8" 
                  placeholder="<?php esc_attr_e( 'WYSIWYG content…', 'efs-wysiwyg' ); ?>">
        </textarea>
        <?php
    }
}