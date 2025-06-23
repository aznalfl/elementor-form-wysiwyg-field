<?php
/**
 * Plugin Name: Elementor Forms – WYSIWYG Field
 * Description: Adds a rich-text (TinyMCE) field type to Elementor Pro forms.
 * Version:     1.0.3
 * Author:      Luke Lanza
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Only run once Elementor Pro is fully initialised.
 */
add_action( 'elementor_pro/init', function() {

    // 1) Make our new type appear in the dropdown.
    add_filter( 'elementor_pro/forms/field_types', function( $types ) {
        $types['wysiwyg'] = __( 'WYSIWYG', 'efs-wysiwyg' );
        return $types;
    } );

    // 2) Register the field class
    add_action( 'elementor_pro/forms/fields/register', function( $registrar ) {
        require_once __DIR__ . '/form-fields/wysiwyg.php';
        $registrar->register( new \EFS\Wysiwyg_Field() );
    } );

}, 20 );