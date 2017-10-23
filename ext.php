<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub;

use phpbb\extension\base;

/**
 * Group Subscription base.
 */
class ext extends base
{
	public function enable_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->enable_notifications('stevotvr.groupsub.notification.type.warn');
				$phpbb_notifications->enable_notifications('stevotvr.groupsub.notification.type.expired');
				return 'notification';
			default:
				return parent::enable_step($old_state);
		}
	}

	public function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->disable_notifications('stevotvr.groupsub.notification.type.warn');
				$phpbb_notifications->disable_notifications('stevotvr.groupsub.notification.type.expired');
				return 'notification';
			default:
				return parent::disable_step($old_state);
		}
	}

	public function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->purge_notifications('stevotvr.groupsub.notification.type.warn');
				$phpbb_notifications->purge_notifications('stevotvr.groupsub.notification.type.expired');
				return 'notification';
			default:
				return parent::purge_step($old_state);
		}
	}
}
