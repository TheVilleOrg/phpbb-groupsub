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

use stevotvr\groupsub\controller\acp_base_interface;

/**
 * Group Subscription main ACP module.
 */
class main_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;

	/**
	 * @var \phpbb\request\request
	 */
	protected $request;

	public function main($id, $mode)
	{
		global $phpbb_container;
		$this->container = $phpbb_container;
		$this->request = $phpbb_container->get('request');

		switch ($mode)
		{
			case 'products':
				$this->manage_products();
			break;
			case 'subscriptions':
				$this->manage_subscriptions();
			break;
			default:
				$this->settings();
			break;
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
	 * Handle the products mode of the module.
	 */
	protected function manage_products()
	{
		$this->tpl_name = 'products';

		$controller = $this->container->get('stevotvr.groupsub.controller.acp.product');
		$controller->set_page_url($this->u_action);

		$action = $this->request->variable('action', '');
		$id = $this->request->variable('id', 0);

		switch ($action)
		{
			case 'add':
				$this->page_title = 'ACP_GROUPSUB_PROD_ADD';
				$controller->add();
				return;
			break;
			case 'edit':
				$this->page_title = 'ACP_GROUPSUB_PROD_EDIT';
				$controller->edit($id);
				return;
			break;
			case 'delete':
				$controller->delete($id);
				return;
			break;
			case 'move_up':
				$controller->move($id, -1);
			break;
			case 'move_down':
				$controller->move($id, 1);
			break;
		}

		$this->page_title = 'ACP_GROUPSUB_MANAGE_PRODS';
		$controller->display();
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
				return;
			break;
			case 'edit':
				$this->page_title = 'ACP_GROUPSUB_SUB_EDIT';
				$controller->edit($id);
				return;
			break;
			case 'delete':
				$controller->delete($id);
				return;
			break;
		}

		$this->page_title = 'ACP_GROUPSUB_MANAGE_SUBS';
		$controller->display();
	}
}
