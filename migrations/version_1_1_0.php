<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\migrations;

use phpbb\db\migration\migration;

/**
 * Group Subscription migration for version 1.1.0.
 */
class version_1_1_0 extends migration
{
	/**
	 * @inheritDoc
	 */
	static public function depends_on()
	{
		return array('\stevotvr\groupsub\migrations\version_0_2_0');
	}

	/**
	 * @inheritDoc
	 */
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'groupsub_subs' => array(
					'sub_paypal_id'	=> array('VCHAR:17', null),
				),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'groupsub_subs' => array(
					'sub_paypal_id',
				),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function update_data()
	{
		return array(
			array('config.add', array('stevotvr_groupsub_allow_auto_renew', false)),
			array('config.add', array('stevotvr_groupsub_pp_sb_api_user', '')),
			array('config.add', array('stevotvr_groupsub_pp_sb_api_pass', '')),
			array('config.add', array('stevotvr_groupsub_pp_sb_api_sig', '')),
			array('config.add', array('stevotvr_groupsub_pp_api_user', '')),
			array('config.add', array('stevotvr_groupsub_pp_api_pass', '')),
			array('config.add', array('stevotvr_groupsub_pp_api_sig', '')),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'groupsub_subs', 'sub_paypal_id');
	}
}
