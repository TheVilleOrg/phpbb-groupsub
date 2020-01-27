<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\migrations;

use phpbb\db\migration\migration;

/**
 * Group Subscription migration for version 1.2.0.
 */
class version_1_2_0 extends migration
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
			'add_tables' => array(
				$this->table_prefix . 'groupsub_actions' => array(
					'COLUMNS' => array(
						'pkg_id'	=> array('UINT', null),
						'act_event'	=> array('UINT', 0),
						'act_name'	=> array('VCHAR:64', ''),
						'act_param'	=> array('TEXT', ''),
					),
					'KEYS' => array(
						'pkg_i'	=> array('INDEX', 'pkg_id'),
					),
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
			'add_tables' => array(
				$this->table_prefix . 'groupsub_groups' => array(
					'COLUMNS' => array(
						'pkg_id'	=> array('UINT', null),
						'sub_id'	=> array('UINT', null),
						'user_id'	=> array('UINT', null),
						'group_id'	=> array('UINT', 0),
						'group_default'	=> array('BOOL', 0),
					),
					'KEYS' => array(
						'k'	=> array('UNIQUE', array('pkg_id', 'sub_id', 'user_id', 'group_id')),
					),
				),
			),
			'drop_tables'   => array(
				$this->table_prefix . 'groupsub_actions',
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function update_data()
	{
		return array(
			array('config.add', array('stevotvr_groupsub_collapse_terms', 6)),
			array('config.add', array('stevotvr_groupsub_notify_admins', false)),
			array('custom', array(array($this, 'update_groups'))),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'groupsub_actions');
	}

	/**
	 * Convert the groups from a previous version to subscription events.
	 */
	public function update_groups()
	{
		$groups_table = $this->table_prefix . 'groupsub_groups';
		$actions_table = $this->table_prefix . 'groupsub_actions';

		$this->db->sql_query('SELECT pkg_id, group_id, group_default FROM ' . $groups_table . ' WHERE pkg_id IS NOT NULL');
		$rows = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult();

		foreach ($rows as $row)
		{
			$data = array(
				'pkg_id'	=> (int) $row['pkg_id'],
				'act_event'	=> 0,
				'act_name'	=> 'gs_add_group',
				'act_param'	=> $row['group_id'],
			);
			$sql = 'INSERT INTO ' . $actions_table . '
			' . $this->db->sql_build_array('INSERT', $data);
			$this->db->sql_query($sql);

			if ($row['group_default'])
			{
				$data['act_name'] = 'gs_default_group';
				$sql = 'INSERT INTO ' . $actions_table . '
				' . $this->db->sql_build_array('INSERT', $data);
				$this->db->sql_query($sql);
			}

			$data['act_event'] = 1;
			$data['act_name'] = 'gs_remove_group';
			$sql = 'INSERT INTO ' . $actions_table . '
			' . $this->db->sql_build_array('INSERT', $data);
			$this->db->sql_query($sql);
		}

		$this->db_tools->sql_table_drop($groups_table);
	}
}
