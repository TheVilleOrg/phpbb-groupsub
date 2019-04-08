<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\migrations;

use phpbb\db\migration\migration;

/**
 * Group Subscription migration for version 0.1.0.
 */
class version_0_1_0 extends migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\v320');
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'groupsub_packages' => array(
					'COLUMNS' => array(
						'pkg_id'					=> array('UINT', null, 'auto_increment'),
						'pkg_ident'					=> array('VCHAR:30', null),
						'pkg_name'					=> array('VCHAR_UNI', ''),
						'pkg_desc'					=> array('TEXT_UNI', ''),
						'pkg_desc_bbcode_uid'		=> array('VCHAR:8', ''),
						'pkg_desc_bbcode_bitfield'	=> array('VCHAR:255', ''),
						'pkg_desc_bbcode_options'	=> array('UINT:11', 7),
						'pkg_order'					=> array('UINT', 0),
						'pkg_enabled'				=> array('BOOL', 0),
						'pkg_deleted'				=> array('BOOL', 0),
					),
					'PRIMARY_KEY' => 'pkg_id',
					'KEYS' => array(
						'pkg_o'	=> array('INDEX', 'pkg_order'),
						'pkg_e'	=> array('INDEX', 'pkg_enabled'),
						'pkg_e'	=> array('INDEX', 'pkg_deleted'),
						'pkg_i'	=> array('UNIQUE', 'pkg_ident'),
					),
				),
				$this->table_prefix . 'groupsub_terms' => array(
					'COLUMNS' => array(
						'term_id'		=> array('UINT', null, 'auto_increment'),
						'pkg_id'		=> array('UINT', 0),
						'term_price'	=> array('UINT', 0),
						'term_currency'	=> array('VCHAR:3', ''),
						'term_length'	=> array('UINT', 0),
						'term_order'	=> array('UINT', 0),
					),
					'PRIMARY_KEY' => 'term_id',
					'KEYS' => array(
						'pkg_i'	=> array('INDEX', 'pkg_id'),
						'pri_o'	=> array('INDEX', 'term_order'),
					),
				),
				$this->table_prefix . 'groupsub_groups' => array(
					'COLUMNS' => array(
						'pkg_id'	=> array('UINT', null),
						'sub_id'	=> array('UINT', null),
						'user_id'	=> array('UINT', null),
						'group_id'	=> array('UINT', 0),
					),
					'KEYS' => array(
						'k'	=> array('UNIQUE', array('pkg_id', 'sub_id', 'user_id', 'group_id')),
					),
				),
				$this->table_prefix . 'groupsub_subs' => array(
					'COLUMNS' => array(
						'sub_id'			=> array('UINT', null, 'auto_increment'),
						'pkg_id'			=> array('UINT', 0),
						'user_id'			=> array('UINT', 0),
						'sub_notify_status'	=> array('USINT', 0),
						'sub_active'		=> array('BOOL', 1),
						'sub_start'			=> array('UINT:11', 0),
						'sub_expires'		=> array('UINT:11', 0),
					),
					'PRIMARY_KEY' => 'sub_id',
					'KEYS' => array(
						'pkg_i'	=> array('INDEX', 'pkg_id'),
						'u_i'	=> array('INDEX', 'user_id'),
						's_a'	=> array('INDEX', 'sub_active'),
						's_e'	=> array('INDEX', 'sub_expires'),
					),
				),
				$this->table_prefix . 'groupsub_trans' => array(
					'COLUMNS' => array(
						'trans_id'			=> array('VCHAR:17', ''),
						'trans_test'		=> array('BOOL', 0),
						'trans_payer'		=> array('VCHAR:13', ''),
						'trans_amount'		=> array('UINT', 0),
						'trans_currency'	=> array('VCHAR:3', ''),
						'trans_time'		=> array('UINT:11', 0),
						'user_id'			=> array('UINT', 0),
						'sub_id'			=> array('UINT', 0),
					),
					'PRIMARY_KEY' => 'trans_id',
					'KEYS' => array(
						't_c'	=> array('INDEX', 'trans_time'),
						'u_i'	=> array('INDEX', 'user_id'),
						's_i'	=> array('INDEX', 'sub_id'),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'   => array(
				$this->table_prefix . 'groupsub_subs',
				$this->table_prefix . 'groupsub_groups',
				$this->table_prefix . 'groupsub_terms',
				$this->table_prefix . 'groupsub_packages',
				$this->table_prefix . 'groupsub_trans',
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('stevotvr_groupsub_active', false)),
			array('config.add', array('stevotvr_groupsub_pp_sandbox', true)),
			array('config.add', array('stevotvr_groupsub_pp_sb_business', '')),
			array('config.add', array('stevotvr_groupsub_pp_business', '')),
			array('config.add', array('stevotvr_groupsub_currency', 'USD')),
			array('config.add', array('stevotvr_groupsub_warn_time', 5)),
			array('config.add', array('stevotvr_groupsub_grace', 5)),
			array('config.add', array('stevotvr_groupsub_cron_last_run', 0)),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_GROUPSUB_TITLE',
			)),
			array('module.add', array(
				'acp',
				'ACP_GROUPSUB_TITLE',
				array(
					'module_basename'	=> '\stevotvr\groupsub\acp\main_module',
					'modes'				=> array('settings', 'packages', 'subscriptions', 'transactions'),
				),
			)),
		);
	}
}
