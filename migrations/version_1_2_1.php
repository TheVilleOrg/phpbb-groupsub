<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\migrations;

use phpbb\db\migration\migration;

/**
 * Group Subscription migration for version 1.2.1.
 */
class version_1_2_1 extends migration
{
	/**
	 * @inheritDoc
	 */
	static public function depends_on()
	{
		return array('\stevotvr\groupsub\migrations\version_1_2_0');
	}

	/**
	 * @inheritDoc
	 */
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_perms'))),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function revert_data()
	{
		return array(
			array('custom', array(array($this, 'revert_perms'))),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function effectively_installed()
	{
		$this->db->sql_query("SELECT 1 FROM " . MODULES_TABLE . " WHERE module_langname = 'ACP_GROUPSUB_SETTINGS' AND module_auth = 'ext_stevotvr/groupsub && acl_a_groupsub_settings'");
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult();

		return (bool) $row;
	}

	/**
	 * Convert the module permissions from a previous version.
	 */
	public function update_perms()
	{
		$this->db->sql_query("UPDATE " . MODULES_TABLE . " SET module_auth = 'ext_stevotvr/groupsub && acl_a_groupsub_settings' WHERE module_langname = 'ACP_GROUPSUB_SETTINGS'");
		$this->db->sql_query("UPDATE " . MODULES_TABLE . " SET module_auth = 'ext_stevotvr/groupsub && acl_a_groupsub_packages' WHERE module_langname = 'ACP_GROUPSUB_MANAGE_PKGS'");
		$this->db->sql_query("UPDATE " . MODULES_TABLE . " SET module_auth = 'ext_stevotvr/groupsub && acl_a_groupsub_subscriptions' WHERE module_langname = 'ACP_GROUPSUB_MANAGE_SUBS'");
		$this->db->sql_query("UPDATE " . MODULES_TABLE . " SET module_auth = 'ext_stevotvr/groupsub && acl_a_groupsub_transactions' WHERE module_langname = 'ACP_GROUPSUB_TRANSACTIONS'");
	}

	/**
	 * Revert the module permissions to a previous version.
	 */
	public function revert_perms()
	{
		$this->db->sql_query("UPDATE " . MODULES_TABLE . " SET module_auth = 'ext_stevotvr/groupsub && acl_a_board' WHERE module_langname = 'ACP_GROUPSUB_SETTINGS'");
		$this->db->sql_query("UPDATE " . MODULES_TABLE . " SET module_auth = 'ext_stevotvr/groupsub && acl_a_board' WHERE module_langname = 'ACP_GROUPSUB_MANAGE_PKGS'");
		$this->db->sql_query("UPDATE " . MODULES_TABLE . " SET module_auth = 'ext_stevotvr/groupsub && acl_a_board' WHERE module_langname = 'ACP_GROUPSUB_MANAGE_SUBS'");
		$this->db->sql_query("UPDATE " . MODULES_TABLE . " SET module_auth = 'ext_stevotvr/groupsub && acl_a_board' WHERE module_langname = 'ACP_GROUPSUB_TRANSACTIONS'");
	}
}
