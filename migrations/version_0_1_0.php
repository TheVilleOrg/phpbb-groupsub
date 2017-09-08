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
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'groupsub' => array(
					'COLUMNS' => array(
						'gs_id'						=> array('UINT', null, 'auto_increment'),
						'gs_ident'					=> array('VCHAR:30', ''),
						'gs_name'					=> array('VCHAR_UNI', ''),
						'gs_desc'					=> array('TEXT_UNI', ''),
						'gs_desc_bbcode_uid'		=> array('VCHAR:8', ''),
						'gs_desc_bbcode_bitfield'	=> array('VCHAR:255', ''),
						'gs_desc_bbcode_options'	=> array('UINT:11', 7),
						'gs_price'					=> array('UINT', 0),
						'gs_length'					=> array('UINT', 0),
						'gs_warn_time'				=> array('UINT', 0),
						'gs_grace'					=> array('UINT', 0),
						'gs_order'					=> array('UINT', 0),
					),
					'PRIMARY_KEY' => 'gs_id',
					'KEYS' => array(
						'gs_order'	=> array('INDEX', 'gs_order'),
						'gs_ident'	=> array('UNIQUE', 'gs_ident'),
					),
				),
				$this->table_prefix . 'groupsub_groups' => array(
					'COLUMNS' => array(
						'gs_id'		=> array('UINT', 0),
						'group_id'	=> array('UINT', 0),
					),
					'PRIMARY_KEY' => array('gs_id', 'group_id'),
				),
				$this->table_prefix . 'groupsub_subs' => array(
					'COLUMNS' => array(
						'sub_id'		=> array('UINT', null, 'auto_increment'),
						'gs_id'			=> array('UINT', 0),
						'user_id'		=> array('UINT', 0),
						'sub_expires'	=> array('UINT', 0),
					),
					'PRIMARY_KEY' => 'sub_id',
					'KEYS' => array(
						'gs_id'			=> array('INDEX', 'gs_id'),
						'user_id'		=> array('INDEX', 'user_id'),
						'sub_expires'	=> array('INDEX', 'sub_expires'),
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
				$this->table_prefix . 'groupsub',
			),
		);
	}
}
