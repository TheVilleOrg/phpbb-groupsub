<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\migrations;

use phpbb\db\migration\migration;

/**
 * Group Subscription migration for version 1.1.1.
 */
class version_1_1_1 extends migration
{
	/**
	 * @inheritDoc
	 */
	static public function depends_on()
	{
		return array('\stevotvr\groupsub\migrations\version_1_1_0');
	}

	/**
	 * @inheritDoc
	 */
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'groupsub_groups' => array(
					'group_type'	=> array('UINT', null),
				),
			),
			'add_columns' => array(
				$this->table_prefix . 'groupsub_groups' => array(
					'group_type'	=> array('UINT', null),
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
				$this->table_prefix . 'groupsub_groups' => array(
					'group_type',
				),
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'groupsub_groups', 'group_type');
	}

	/**
	 * @inheritDoc
	 */
	public function update_data()
	{
		return array(
			// Update groupsub_groups for group_type
			array('custom', array(array($this, 'update_groupsub_groups_table'))),
		);
	}

	public function update_groupsub_groups_table()
	{
		$groupsub_groups = $this->table_prefix . 'groupsub_groups';

		$sql = 'UPDATE '. $groupsub_groups . ' SET group_type = 2';
		$this->db->sql_query($sql);
	}
}
