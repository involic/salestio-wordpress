<?php
/**
 * Hook interface for plugin lifecycle events.
 *
 * @package Salestio
 */

namespace Salestio\Hooks;

/**
 * Hook interface.
 */
interface WooHooks {

	/**
	 * Execute the hook.
	 *
	 * @return void
	 */
	public function execute();
}
