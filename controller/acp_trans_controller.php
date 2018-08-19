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

use phpbb\pagination;
use phpbb\user;
use stevotvr\groupsub\operator\transaction_interface;

/**
 * Group Subscription transactions ACP controller.
 */
class acp_trans_controller extends acp_base_controller implements acp_trans_interface
{
	/**
	 * @var \phpbb\pagination
	 */
	protected $pagination;

	/**
	 * @var \stevotvr\groupsub\operator\transaction_interface
	 */
	protected $trans_operator;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * Set up the controller.
	 *
	 * @param \phpbb\pagination                                 $pagination
	 * @param \stevotvr\groupsub\operator\transaction_interface $sub_operator
	 * @param \phpbb\user                                       $user
	 */
	public function setup(pagination $pagination, transaction_interface $trans_operator, user $user)
	{
		$this->pagination = $pagination;
		$this->trans_operator = $trans_operator;
		$this->user = $user;
	}

	public function display()
	{
		$sort_key = $sort_dir = '';
		$start = $limit = 0;
		$params = $this->parse_display_params($sort_key, $sort_dir, $start, $limit);

		if ($this->request->is_set_post('sort') || $this->request->is_set_post('filter'))
		{
			redirect($this->u_action . $params);
			return;
		}

		$this->load_sort_options($sort_key, $sort_dir);

		$u_sub = str_replace('mode=transactions', 'mode=subscriptions&amp;action=edit&amp;id=', $this->u_action);
		$transactions = $this->trans_operator->get_transactions($start, $limit, $this->get_sort_field($sort_key), ($sort_dir === 'd'));
		foreach ($transactions as $transaction)
		{
			$this->template->assign_block_vars('transaction', array(
				'ID'		=> $transaction['trans_id'],
				'TEST'		=> (bool) $transaction['trans_test'],
				'AMOUNT'	=> $this->currency->format_price($transaction['trans_currency'], $transaction['trans_amount']),
				'TIME'		=> $this->user->format_date($transaction['trans_time']),
				'USER'		=> $transaction['username'],

				'U_SUBSCRIPTION'	=> $u_sub . $transaction['sub_id'],
			));
		}

		$this->template->assign_vars(array(
			'LIMIT'	=> $limit,

			'U_ACTION'	=> $this->u_action . $params,
		));

		$total = $this->trans_operator->count_transactions();
		$this->pagination->generate_template_pagination($this->u_action, 'pagination', 'start', $total, $limit, $start);
	}

	/**
	 * Load the sorting options into template variables.
	 *
	 * @param string $sort_key The current sort key value
	 * @param string $sort_dir The current sort direction value
	 */
	protected function load_sort_options($sort_key, $sort_dir)
	{
		$options = array(
			't'	=> $this->language->lang('ACP_GROUPSUB_TIME'),
			'u'	=> $this->language->lang('ACP_GROUPSUB_USER'),
			'a'	=> $this->language->lang('ACP_GROUPSUB_AMOUNT'),
		);
		foreach ($options as $key => $name)
		{
			$this->template->assign_block_vars('sort_key', array(
				'KEY'	=> $key,
				'NAME'	=> $name,

				'S_SELECTED'	=> ($key === $sort_key),
			));
		}

		$options = array(
			'a'	=> $this->language->lang('ASCENDING'),
			'd'	=> $this->language->lang('DESCENDING'),
		);
		foreach ($options as $key => $name)
		{
			$this->template->assign_block_vars('sort_dir', array(
				'KEY'	=> $key,
				'NAME'	=> $name,

				'S_SELECTED'	=> ($key === $sort_dir),
			));
		}
	}

	/**
	 * Translate the sort key into the name of the database column.
	 *
	 * @param string $sort_key The sort key
	 *
	 * @return string The name of the database column
	 */
	protected function get_sort_field($sort_key)
	{
		switch ($sort_key)
		{
			case 'u':
				return 'u.username';
			case 'a':
				return 't.trans_amount';
		}

		return 't.trans_time';
	}

	/**
	 * Parse the URL parameters for the main list display options.
	 *
	 * @param string &$sort_key   Variable to hold the value of the sort key parameters
	 * @param string &$sort_dir   Variable to hold the value of the sort direction parameters
	 * @param int    &$start      Variable to hold the value of the start parameters
	 * @param int    &$limit      Variable to hold the value of the limit parameters
	 *
	 * @return string The reconstructed parameter string
	 */
	protected function parse_display_params(&$sort_key = '', &$sort_dir = '', &$start = 0, &$limit = 0)
	{
		$sort_key = $this->request->variable('sk', 't');
		$sort_dir = $this->request->variable('sd', 'a');
		$start = $this->request->variable('start', 0);
		$limit = min(100, $this->request->variable('limit', (int) $this->config['topics_per_page']));

		return sprintf(
			'&amp;sk=%s&amp;sd=%s&amp;start=%d&amp;limit=%d',
			$sort_key,
			$sort_dir,
			$start,
			$limit
		);
	}
}
