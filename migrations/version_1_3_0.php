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
 * Group Subscription migration for version 1.3.0.
 */
class version_1_3_0 extends migration
{
	/**
	 * @inheritDoc
	 */
	static public function depends_on()
	{
		return array('\stevotvr\groupsub\migrations\version_1_2_1');
	}
	/**
	 * @inheritDoc
	 */
	public function update_data()
	{
		return array(
			array('config.remove', array('stevotvr_groupsub_pp_sb_business')),
			array('config.remove', array('stevotvr_groupsub_pp_business')),

			array('config.add', array('stevotvr_groupsub_pp_client', '')),
			array('config.add', array('stevotvr_groupsub_pp_secret', '')),
			array('config.add', array('stevotvr_groupsub_sb_client', '')),
			array('config.add', array('stevotvr_groupsub_sb_secret', '')),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function effectively_installed()
	{
		return isset($this->config['stevotvr_groupsub_pp_client']);
	}
}
