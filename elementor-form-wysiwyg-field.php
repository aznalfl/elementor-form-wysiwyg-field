<?php
/**
 * Plugin Name: Elementor Forms – WYSIWYG Field
 * Description: Adds a TinyMCE WYSIWYG field type to Elementor Pro forms.
 * Version:     2.0.0
 * Author:      Luke Lanza
 * Text Domain: efs-wysiwyg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the TinyMCE CDN script once.
 * (TinyMCE 6 is ~150 kB and loads only on pages that actually contain the form.)
 */
add_action( 'init', function () {
	wp_register_script(
		'tinymce-cdn',
		'https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js',
		[],
		null,
		true
	);
} );

/**
 * Expose “WYSIWYG” in the Form-widget Type dropdown.
 */
add_filter( 'elementor_pro/forms/field_types', function ( $types ) {
	$types['wysiwyg'] = __( 'WYSIWYG', 'efs-wysiwyg' );
	return $types;
}, 5 );

/**
 * Register our custom field class.
 */
add_action( 'elementor_pro/forms/fields/register', function ( $registrar ) {
	require_once __DIR__ . '/form-fields/wysiwyg.php';
	$registrar->register( new \EFS\Wysiwyg_Field() );
}, 5 );