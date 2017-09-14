<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\controller;

/**
 * Group Subscription ACP settings controller interface.
 */
interface acp_settings_interface extends acp_base_interface
{
	/**
	 * Handle the settings form.
	 */
	public function handle();
}
