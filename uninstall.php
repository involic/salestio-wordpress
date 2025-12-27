<?php
/**
 * Uninstall cleanup.
 *
 * @package Salestio
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once __DIR__ . '/src/Services/class-optionaccessor.php';

$option_accessor = new Salestio\Services\OptionAccessor();
$option_accessor->delete_options();
