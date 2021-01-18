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
 * Group Subscription main ACP module.
 */
class main_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var request
	 */
	protected $request;

	public function main($id, $mode)
	{
		global $phpbb_container;
		$this->container = $phpbb_container;
		$this->request = $phpbb_container->get('request');

		switch ($mode)
		{
			case 'packages':
				$this->manage_packages();
			break;
			case 'subscriptions':
				$this->manage_subscriptions();
			break;
			case 'transactions':
				$this->manage_transactions();
			break;
			default:
				$this->settings();
		}
	}

	/**
	 * Handle the settings mode of the module.
	 */
	protected function settings()
	{
		$this->tpl_name = 'settings';
		$this->page_title = 'ACP_GROUPSUB_SETTINGS_TITLE';

		$controller = $this->container->get('stevotvr.groupsub.controller.acp.settings');
		$controller->set_page_url($this->u_action);
		$controller->handle();
	}

	/**
	 * Handle the packages mode of the module.
	 */
	protected function manage_packages()
	{
		$this->tpl_name = 'packages';

		$controller = $this->container->get('stevotvr.groupsub.controller.acp.package');
		$controller->set_page_url($this->u_action);

		$action = $this->request->variable('action', '');
		$id = $this->request->variable('id', 0);

		switch ($action)
		{
			case 'add':
				$this->page_title = 'ACP_GROUPSUB_PKG_ADD';
				$controller->add();
			break;
			case 'edit':
				$this->page_title = 'ACP_GROUPSUB_PKG_EDIT';
				$controller->edit($id);
			break;
			case 'delete':
				$controller->delete($id);
			break;
			case 'move_up':
				$controller->move($id, -1);
			break;
			case 'move_down':
				$controller->move($id, 1);
			break;
		}

		if (!in_array($action, array('add', 'edit', 'delete')))
		{
			$this->page_title = 'ACP_GROUPSUB_MANAGE_PKGS';
			$controller->display();
		}
	}

	/**
	 * Handle the subscriptions mode of the module.
	 */
	protected function manage_subscriptions()
	{
		$this->tpl_name = 'subscriptions';

		$controller = $this->container->get('stevotvr.groupsub.controller.acp.subscription');
		$controller->set_page_url($this->u_action);

		$action = $this->request->variable('action', '');
		$id = $this->request->variable('id', 0);

		switch ($action)
		{
			case 'add':
				$this->page_title = 'ACP_GROUPSUB_SUB_ADD';
				$controller->add();
			break;
			case 'edit':
				$this->page_title = 'ACP_GROUPSUB_SUB_EDIT';
				$controller->edit($id);
			break;
			case 'delete':
				$controller->delete($id);
			break;
			case 'restart':
				$controller->restart($id);
			break;
		}

		if (!in_array($action, array('add', 'edit', 'delete', 'restart')))
		{
			$this->page_title = 'ACP_GROUPSUB_MANAGE_SUBS';
			$controller->display();
		}
	}

	/**
	 * Handle the transactions mode of the module.
	 */
	protected function manage_transactions()
	{
		$this->tpl_name = 'transactions';

		$controller = $this->container->get('stevotvr.groupsub.controller.acp.transaction');
		$controller->set_page_url($this->u_action);

		$this->page_title = 'ACP_GROUPSUB_TRANSACTIONS';
		$controller->display();
	}
}
