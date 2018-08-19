<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\operator;

/**
 * Group Subscription transaction operator interface.
 */
interface transaction_interface
{
	/**
	 * The status for a completed payment
	 */
	const STATUS_COMPLETED = 'Completed';

	/**
	 * Process a transaction from PayPal
	 *
	 * @return boolean The transaction was accepted
	 */
	public function process_transaction();
}
