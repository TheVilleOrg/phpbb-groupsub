<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\event;

use phpbb\auth\auth;
use phpbb\event\data;
use phpbb\request\request_interface;
use stevotvr\groupsub\controller\acp_subs_interface as sub_controller;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Group Subscription ACP event listener.
 */
class acp_listener implements EventSubscriberInterface
{
	/**
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \stevotvr\groupsub\controller\acp_subs_interface
	 */
	protected $sub_controller;

	/**
	 * @param \phpbb\auth\auth                                 $auth
	 * @param \phpbb\request\request_interface                 $request
	 * @param \stevotvr\groupsub\controller\acp_subs_interface $sub_controller
	 */
	public function __construct(auth $auth, request_interface $request, sub_controller $sub_controller)
	{
		$this->auth = $auth;
		$this->request = $request;
		$this->sub_controller = $sub_controller;
	}

	/**
	 * @inheritDoc
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_users_mode_add'	=> 'acp_users_mode_add',
		);
	}

	/**
	 * Adds subscriptions mode to the ACP user management form.
	 *
	 * @param \phpbb\event\data $event The event data
	 */
	public function acp_users_mode_add(data $event)
	{
		if ($event['mode'] === 'groupsub' && $event['user_id'])
		{
			$this->sub_controller->set_user($event['user_id']);

			$action = $this->request->variable('action', '');
			$id = $this->request->variable('id', 0);

			switch ($action)
			{
				case 'add':
					$this->sub_controller->add();
				break;
				case 'edit':
					$this->sub_controller->edit($id);
				break;
				case 'delete':
					$this->sub_controller->delete($id);
				break;
				default:
					$this->sub_controller->display();
			}
		}
	}
}
