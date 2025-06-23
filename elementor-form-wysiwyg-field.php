<?php
/**
 * Plugin Name: Elementor Forms – WYSIWYG Field
 * Description: Adds a rich-text (TinyMCE) field type to Elementor Pro forms.
 * Version: 1.0.0
 * Author: Luke Lanza
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Ensure Elementor Pro is active.
 */
add_action( 'plugins_loaded', function () {

	if ( ! class_exists( '\ElementorPro\Modules\Forms\Module' ) ) {
		return; // Pro not active, bail quietly.
	}

	// Register the field.
	add_action( 'elementor_pro/forms/fields/register', function ( $registrar ) {

		require_once __DIR__ . '/form-fields/wysiwyg.php';
		$registrar->register( new \EFS\Wysiwyg_Field() );

	} );
} );