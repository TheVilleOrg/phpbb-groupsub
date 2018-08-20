<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\acp;

/**
 * Group Subscription main ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\stevotvr\groupsub\acp\main_module',
			'title'		=> 'ACP_GROUPSUB_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_GROUPSUB_SETTINGS',
					'auth'	=> 'ext_stevotvr/groupsub && acl_a_board',
					'cat'	=> array('ACP_GROUPSUB_TITLE'),
				),
				'packages'	=> array(
					'title'	=> 'ACP_GROUPSUB_MANAGE_PKGS',
					'auth'	=> 'ext_stevotvr/groupsub && acl_a_board',
					'cat'	=> array('ACP_GROUPSUB_TITLE'),
				),
				'subscriptions'	=> array(
					'title'	=> 'ACP_GROUPSUB_MANAGE_SUBS',
					'auth'	=> 'ext_stevotvr/groupsub && acl_a_board',
					'cat'	=> array('ACP_GROUPSUB_TITLE'),
				),
				'transactions'	=> array(
					'title'	=> 'ACP_GROUPSUB_TRANSACTIONS',
					'auth'	=> 'ext_stevotvr/groupsub && acl_a_board',
					'cat'	=> array('ACP_GROUPSUB_TITLE'),
				),
			),
		);
	}
}
