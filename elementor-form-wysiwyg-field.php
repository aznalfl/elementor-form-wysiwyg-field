<?php
/**
 * Plugin Name: Elementor Forms – WYSIWYG Field
 * Description: Adds a TinyMCE WYSIWYG field type to Elementor Pro forms.
 * Version:     1.2.0
 * Author:      Luke Lanza
 * Text Domain: efs-wysiwyg
 *
 * Requires Plugins: elementor, elementor-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1) Register our JS bundle so we can enqueue it everywhere.
 */
add_action( 'init', function () {
    wp_register_script(
        'efs-wysiwyg', 
        plugins_url( 'assets/wysiwyg-field.js', __FILE__ ),
        [ 'jquery', 'wp-editor', 'tinymce', 'elementor-frontend' ],
        '1.2.0',
        true
    );
} );

/**
 * 2) Enqueue in the Elementor editor (real-time builder & preview).
 */
add_action( 'elementor/editor/after_enqueue_scripts', function() {
    wp_enqueue_editor();
    wp_enqueue_script( 'efs-wysiwyg' );
} );
add_action( 'elementor/preview/enqueue_scripts', function() {
    wp_enqueue_editor();
    wp_enqueue_script( 'efs-wysiwyg' );
} );

/**
 * 3) Add “WYSIWYG” to the Form widget’s Type dropdown.
 */
add_filter( 'elementor_pro/forms/field_types', function( $types ) {
    $types['wysiwyg'] = __( 'WYSIWYG', 'efs-wysiwyg' );
    return $types;
}, 5 );

/**
 * 4) Register the field class early so Elementor picks it up immediately.
 */
add_action( 'elementor_pro/forms/fields/register', function( $registrar ) {
    require_once __DIR__ . '/form-fields/wysiwyg.php';
    $registrar->register( new \EFS\Wysiwyg_Field() );
}, 5, 1 );