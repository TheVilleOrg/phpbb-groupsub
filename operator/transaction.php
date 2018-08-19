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

use phpbb\config\config;
use phpbb\request\request_interface;
use stevotvr\groupsub\operator\currency_interface;
use stevotvr\groupsub\operator\subscription_interface;

/**
 * Group Subscription transaction operator.
 */
class transaction extends operator implements transaction_interface
{
	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \stevotvr\groupsub\operator\currency_interface
	 */
	protected $currency;

	/**
	 * @var \stevotvr\groupsub\operator\subscription_interface
	 */
	protected $sub_operator;

	/**
	 * @var string The name of the groupsub_trans table
	 */
	protected $trans_table;

	/**
	 * Set up the operator.
	 *
	 * @param \phpbb\config\config                               $config
	 * @param \phpbb\request\request_interface                   $request
	 * @param \stevotvr\groupsub\operator\currency_interface     $currency
	 * @param \stevotvr\groupsub\operator\subscription_interface $sub_operator
	 * @param string                                             $trans_table The name of the
	 *                                                                        groupsub_trans table
	 */
	public function setup(config $config, request_interface $request, currency_interface $currency, subscription_interface $sub_operator, $trans_table)
	{
		$this->config = $config;
		$this->request = $request;
		$this->currency = $currency;
		$this->sub_operator = $sub_operator;
		$this->trans_table = $trans_table;
	}

	public function process_transaction()
	{
		$sandbox = (bool) $this->config['stevotvr_groupsub_pp_sandbox'];
		if ($sandbox !== $this->request->variable('test_ipn', false))
		{
			return false;
		}

		$business = $this->config[$sandbox ? 'stevotvr_groupsub_pp_sb_business' : 'stevotvr_groupsub_pp_business'];
		if ($business !== $this->request->variable('business', ''))
		{
			return false;
		}

		if ($this->request->variable('payment_status', '') !== self::STATUS_COMPLETED)
		{
			return true;
		}

		$term_id = $this->request->variable('item_number', 0);
		$term = $this->container->get('stevotvr.groupsub.entity.term')->load($term_id);

		$currency = $this->request->variable('mc_currency', '');
		if ($term->get_currency() !== $currency)
		{
			return false;
		}

		$gross = $this->request->variable('mc_gross', '');
		$amount = $this->currency->parse_value($currency, $gross);
		if ($term->get_price() !== $amount)
		{
			return false;
		}

		$trans_id = $this->request->variable('txn_id', '');
		$sql = 'SELECT 1
				FROM ' . $this->trans_table . "
				WHERE trans_id = '" . $this->db->sql_escape($trans_id) . "'";
		$this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult();

		if ($row)
		{
			return false;
		}

		$user_id = $this->request->variable('custom', 0);
		$sub_id = $this->sub_operator->create_subscription($term, $user_id);

		return $this->insert_transaction($trans_id, $sandbox, $amount, $currency, $user_id, $sub_id);
	}

	/**
	 * Insert a transaction into the database.
	 *
	 * @param string  $trans_id The transaction ID
	 * @param boolean $sandbox  Sandbox mode is enabled
	 * @param int     $amount   The payment amount in the currency subunit
	 * @param string  $currency The currency code
	 * @param int     $user_id  The user ID
	 * @param int     $sub_id   The subscription ID
	 *
	 * @return boolean The record was inserted successfully
	 */
	protected function insert_transaction($trans_id, $sandbox, $amount, $currency, $user_id, $sub_id)
	{
		if (!preg_match('/^[A-Z0-9]{17}$/', $trans_id))
		{
			return false;
		}

		$payer_id = $this->request->variable('payer_id', '');
		if (!preg_match('/^[A-Z0-9]{13}$/', $payer_id))
		{
			return false;
		}

		if (!isset($this->currencies[$currency]))
		{
			return false;
		}

		$data = array(
			'trans_id'			=> $trans_id,
			'trans_test'		=> (bool) $sandbox,
			'trans_payer'		=> $payer_id,
			'trans_amount'		=> (int) $amount,
			'trans_currency'	=> $currency,
			'trans_time'		=> time(),
			'user_id'			=> (int) $user_id,
			'sub_id'			=> (int) $sub_id,
		);
		$sql = 'INSERT INTO ' . $this->trans_table . '
				' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);

		return true;
	}
}
