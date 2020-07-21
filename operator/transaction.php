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

use phpbb\event\dispatcher_interface;
use phpbb\log\log_interface;
use phpbb\request\request_interface;
use stevotvr\groupsub\operator\currency_interface;
use stevotvr\groupsub\operator\subscription_interface;

/**
 * Group Subscription transaction operator.
 */
class transaction extends operator implements transaction_interface
{
	/**
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $phpbb_dispatcher;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \phpbb\log\log_interface
	 */
	protected $log;

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
	 * The name of the phpBB users table.
	 *
	 * @var string
	 */
	protected $phpbb_users_table;

	/**
	 * Set up the operator.
	 *
	 * @param \phpbb\request\request_interface                   $request
	 * @param \phpbb\log\log_interface                           $log
	 * @param \stevotvr\groupsub\operator\currency_interface     $currency
	 * @param \phpbb\event\dispatcher_interface                  $phpbb_dispatcher
	 * @param \stevotvr\groupsub\operator\subscription_interface $sub_operator
	 * @param string                                             $trans_table The name of the
	 *                                                                        groupsub_trans table
	 * @param string                                             $phpbb_users_table    The name of the phpBB users table
	 */
	public function setup(request_interface $request, log_interface $log, currency_interface $currency, dispatcher_interface $phpbb_dispatcher, subscription_interface $sub_operator, $trans_table, $phpbb_users_table)
	{
		$this->request = $request;
		$this->log = $log;
		$this->currency = $currency;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->sub_operator = $sub_operator;
		$this->trans_table = $trans_table;
		$this->phpbb_users_table = $phpbb_users_table;
	}

	/**
	 * @inheritDoc
	 */
	public function process_transaction()
	{
		if ($this->request->variable('txn_type', '') !== 'web_accept')
		{
			return false;
		}

		$sandbox = (bool) $this->config['stevotvr_groupsub_pp_sandbox'];
		if ($sandbox !== $this->request->variable('test_ipn', false))
		{
			return false;
		}

		$business = $this->config[$sandbox ? 'stevotvr_groupsub_pp_sb_business' : 'stevotvr_groupsub_pp_business'];
		if (strcasecmp($business, $this->request->variable('business', '')) !== 0)
		{
			return false;
		}

		if ($this->request->variable('payment_status', '') !== self::STATUS_COMPLETED)
		{
			return true;
		}

		$term_id = $this->request->variable('item_number', 0);
		$term = $this->container->get('stevotvr.groupsub.entity.term')->load($term_id);
		if (!$term)
		{
			$this->log->add('critical', ANONYMOUS, false, 'LOG_GROUPSUB_TRANS_NO_TERM', false, array($term_id));
			return false;
		}

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

		return $this->insert_transaction($trans_id, $sandbox, $amount, $currency, $user_id, $sub_id, $gross);
	}

	/**
	 * @inheritDoc
	 */
	public function get_transactions($start, $limit, $sort_field, $sort_desc)
	{
		$sql_ary = array(
			'SELECT'	=> 't.*, u.username, u.user_colour',
			'FROM'		=> array(
				$this->trans_table => 't',
				$this->phpbb_users_table => 'u',
			),
			'WHERE'		=> 't.user_id = u.user_id',
			'ORDER_BY'	=> $sort_field . ($sort_desc ? ' DESC' : ' ASC'),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$this->db->sql_query_limit($sql, $limit, $start);
		$rows = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult();

		return $rows;
	}

	/**
	 * @inheritDoc
	 */
	public function count_transactions()
	{
		$sql = 'SELECT COUNT(trans_id) AS trans_count
				FROM ' . $this->trans_table;
		$this->db->sql_query($sql);
		$count = $this->db->sql_fetchfield('trans_count');
		$this->db->sql_freeresult();

		return (int) $count;
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
	 * @param string  $gross    The gross payment amount
	 *
	 * @return boolean The record was inserted successfully
	 */
	protected function insert_transaction($trans_id, $sandbox, $amount, $currency, $user_id, $sub_id, $gross)
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

		$txn_id = $trans_id;
		$test_ipn = $sandbox;
		$mc_currency = $currency;
		$mc_gross = $gross;

		/**
		 * Event triggered when a payment is received.
		 *
		 * @event stevotvr.groupsub.payment_received
		 * @var int     user_id     The user ID
		 * @var int     sub_id      The subscription ID
		 * @var string  txn_id      The PayPal transaction ID
		 * @var string  payer_id    The PayPal payer ID
		 * @var boolean test_ipn    True if sandbox mode is enabled, otherwise false
		 * @var string  mc_currency The three character currency code
		 * @var string  mc_gross    The gross amount of the payment
		 * @since 1.1.0
		 */
		$vars = array(
			'user_id',
			'sub_id',
			'txn_id',
			'payer_id',
			'test_ipn',
			'mc_currency',
			'mc_gross',
		);
		extract($this->phpbb_dispatcher->trigger_event('stevotvr.groupsub.payment_received', compact($vars)));

		return true;
	}
}
