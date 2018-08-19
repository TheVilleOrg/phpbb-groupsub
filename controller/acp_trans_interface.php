<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\controller;

/**
 * Group Subscription ACP transactions controller interface.
 */
interface acp_trans_interface extends acp_base_interface
{
	/**
	 * Display all items.
	 */
	public function display();
}
