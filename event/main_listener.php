<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\event;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver_interface;
use phpbb\event\data;
use phpbb\template\template;
use stevotvr\groupsub\operator\package_interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Group Subscription event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/**
	 * @var auth
	 */
	protected $auth;

	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var driver_interface
	 */
	protected $db;

	/**
	 * @var helper
	 */
	protected $helper;

	/**
	 * @var package_interface
	 */
	protected $pkg_operator;

	/**
	 * @var template
	 */
	protected $template;

	/**
	 * The name of the groupsub_actions table.
	 *
	 * @var string
	 */
	protected $action_table;

	/**
	 * The name of the groupsub_subs table.
	 *
	 * @var string
	 */
	protected $sub_table;

	/**
	 * @param auth              $auth
	 * @param config            $config
	 * @param driver_interface  $db
	 * @param helper            $helper
	 * @param package_interface $pkg_operator
	 * @param template          $template
	 * @param string            $action_table The name of the groupsub_actions table
	 * @param string            $sub_table    The name of the groupsub_subs table
	 */
	public function __construct(auth $auth, config $config, driver_interface $db, helper $helper, package_interface $pkg_operator, template $template, $action_table, $sub_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->pkg_operator = $pkg_operator;
		$this->template = $template;
		$this->action_table = $action_table;
		$this->sub_table = $sub_table;
	}

	/**
	 * @inheritDoc
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.permissions'			=> 'permissions',
			'core.user_setup'			=> 'user_setup',
			'core.delete_group_after'	=> 'delete_group_after',
			'core.delete_user_after'	=> 'delete_user_after',
		);
	}

	/**
	 * Loads the permissions.
	 *
	 * @param data $event The event data
	 */
	public function permissions(data $event)
	{
		$categories = $event['categories'];
		$categories['groupsub'] = 'ACL_CAT_GROUPSUB';
		$event['categories'] = $categories;

		$permissions = $event['permissions'];
		$permissions['a_groupsub_settings'] = array('lang' => 'ACL_A_GROUPSUB_SETTINGS', 'cat' => 'groupsub');
		$permissions['a_groupsub_packages'] = array('lang' => 'ACL_A_GROUPSUB_PACKAGES', 'cat' => 'groupsub');
		$permissions['a_groupsub_subscriptions'] = array('lang' => 'ACL_A_GROUPSUB_SUBSCRIPTIONS', 'cat' => 'groupsub');
		$permissions['a_groupsub_subscriptions_edit'] = array('lang' => 'ACL_A_GROUPSUB_SUBSCRIPTIONS_EDIT', 'cat' => 'groupsub');
		$permissions['a_groupsub_transactions'] = array('lang' => 'ACL_A_GROUPSUB_TRANSACTIONS', 'cat' => 'groupsub');
		$event['permissions'] = $permissions;
	}

	/**
	 * Adds the extension language set and the controller link on user setup.
	 *
	 * @param data $event The event data
	 */
	public function user_setup(data $event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name'	=> 'stevotvr/groupsub',
			'lang_set'	=> 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;

		if (($this->config['stevotvr_groupsub_active'] || $this->auth->acl_get('a_')) && $this->pkg_operator->count_packages())
		{
			$this->template->assign_var('U_GROUPSUB_SUBS', $this->helper->route('stevotvr_groupsub_main'));
		}
	}

	/**
	 * Remove references to a group after it is deleted.
	 *
	 * @param data $event The event data
	 */
	public function delete_group_after(data $event)
	{
		$actions = array(
			'gs_add_group',
			'gs_remove_group',
			'gs_default_group',
		);
		$sql = 'DELETE FROM ' . $this->action_table . '
				WHERE ' . $this->db->sql_in_set('act_name', $actions) . '
					AND act_param = ' . (int) $event['group_id'];
		$this->db->sql_query($sql);
	}

	/**
	 * Remove references to users after they are deleted.
	 *
	 * @param data $event The event data
	 */
	public function delete_user_after(data $event)
	{
		$sql = 'DELETE FROM ' . $this->sub_table . '
				WHERE ' . $this->db->sql_in_set('user_id', $event['user_ids']);
		$this->db->sql_query($sql);
	}
}
