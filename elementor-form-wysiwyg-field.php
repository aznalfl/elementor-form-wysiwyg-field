<?php
/**
 * Plugin Name: Elementor Forms WYSIWYG Field
 * Description: Adds a TinyMCE-rich-text field type to Elementor Pro Forms.
 * Author:      Luke Lanza
 * Version:     2.0.0
 * Requires Plugins: elementor, elementor-pro
 * Elementor tested up to: 3.25.0
 * Elementor Pro tested up to: 3.25.0
 * Text Domain: elementor-form-wysiwyg-field
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Register the new field type with Elementor Pro’s registrar.
 *
 * @param \ElementorPro\Modules\Forms\Registrars\Form_Fields_Registrar $registrar
 */
function efwf_register_wysiwyg_field( $registrar ) {

	require_once __DIR__ . '/form-fields/wysiwyg.php';

	$registrar->register( new \EFWF_Wysiwyg_Field() );
}
add_action( 'elementor_pro/forms/fields/register', 'efwf_register_wysiwyg_field' );
