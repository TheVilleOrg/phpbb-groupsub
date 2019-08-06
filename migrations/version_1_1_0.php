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
	public function update_data()
	{
		return array(
			array('config_text.add', array('stevotvr_groupsub_header', '')),
			array('config.add', array('stevotvr_groupsub_header_bbcode_uid', '')),
			array('config.add', array('stevotvr_groupsub_header_bbcode_bitfield', '')),
			array('config.add', array('stevotvr_groupsub_header_bbcode_options', 7)),
			array('config_text.add', array('stevotvr_groupsub_footer', '')),
			array('config.add', array('stevotvr_groupsub_footer_bbcode_uid', '')),
			array('config.add', array('stevotvr_groupsub_footer_bbcode_bitfield', '')),
			array('config.add', array('stevotvr_groupsub_footer_bbcode_options', 7)),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function effectively_installed()
	{
		return isset($this->config['stevotvr_groupsub_header_bbcode_uid']);
	}
}
