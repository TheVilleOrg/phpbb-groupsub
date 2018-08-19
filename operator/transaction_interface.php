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

	/**
	 * Get transactions.
	 *
	 * @param int     $start      The offset of the first row to return
	 * @param int     $limit      The limit of the number of rows to return
	 * @param string  $sort_field The name of the field by which to sort
	 * @param boolean $sort_desc  Sort in descending order
	 *
	 * @return array The rows from the database
	 */
	public function get_transactions($start, $limit, $sort_field, $sort_desc);

	/**
	 * Count the total number of transactions.
	 *
	 * @return int The total number of transactions
	 */
	public function count_transactions();
}
