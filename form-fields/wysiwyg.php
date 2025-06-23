<?php
namespace EFS;

use ElementorPro\Modules\Forms\Fields\Field_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wysiwyg_Field extends Field_Base {

    /*--------------------------------------------------------------
    # Basic meta
    --------------------------------------------------------------*/
    public function get_type(): string {
        return 'wysiwyg';
    }

    public function get_name(): string {
        return __( 'WYSIWYG', 'efs-wysiwyg' );
    }

    /*--------------------------------------------------------------
    # Scripts
    --------------------------------------------------------------*/
    public function get_script_depends(): array {
        // WordPress core TinyMCE bundle
        wp_enqueue_editor();

        // Our initialiser
        wp_register_script(
            'efs-wysiwyg',
            plugins_url( '../assets/wysiwyg-field.js', __FILE__ ),
            [ 'jquery', 'tinymce', 'elementor-frontend' ],
            '1.0.1',
            true
        );

        return [ 'efs-wysiwyg' ];
    }

    /*--------------------------------------------------------------
    # Front-end render
    --------------------------------------------------------------*/
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

    /*--------------------------------------------------------------
    # Validation / sanitisation
    --------------------------------------------------------------*/
    public function validation( $field, $record, $ajax_handler ): void {
        $field['value'] = wp_kses_post( $field['value'] );
    }

    /*--------------------------------------------------------------
    # Editor-side preview template
    --------------------------------------------------------------*/
    public function content_template() {
        ?>
        <#
        // _sub_fields_index is Elementor’s internal counter for the field
        var fieldId   = 'form-field-' + data._sub_fields_index,
            cssClass  = 'elementor-field elementor-wysiwyg-field ' + ( data.css_classes || '' );
        #>

        <textarea id="{{ fieldId }}" class="{{ cssClass }}" rows="8"
                  placeholder="<?php echo esc_attr__( 'WYSIWYG content…', 'efs-wysiwyg' ); ?>">
        </textarea>
        <?php
    }
}