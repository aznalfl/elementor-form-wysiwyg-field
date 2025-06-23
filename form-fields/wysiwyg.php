<?php
namespace EFS;
use ElementorPro\Modules\Forms\Fields\Field_Base;

if ( ! defined( 'ABSPATH' ) ) exit;

class Wysiwyg_Field extends Field_Base {

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
		wp_enqueue_editor();               // loads core TinyMCE files
		wp_register_script(
			'efs-wysiwyg',
			plugins_url( '../assets/wysiwyg-field.js', __FILE__ ),
			[ 'tinymce', 'elementor-frontend' ],
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
			'id'         => $field_id,
			'name'       => $item['field_name'],
			'class'      => 'elementor-field elementor-wysiwyg-field',
			'rows'       => '10',
			'required'   => $item['required'] ? 'required' : false,
		] );

		echo '<textarea ' . $form->get_render_attribute_string( $field_id ) . '></textarea>';
	}

	/**
	 * Basic validation – strip unsafe tags.
	 */
	public function validation( $field, $record, $ajax_handler ): void {
		$field['value'] = wp_kses_post( $field['value'] );
	}
}