<?php
/**
 * Boilerplate theme boot file.
 *
 * @package WordPress Boilerplate
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Require dependencies manged by composer.
require_once ABSPATH . 'vendor/autoload.php';

// Load assets.
require_once __DIR__ . '/includes/class-assets.php';
new Assets();
