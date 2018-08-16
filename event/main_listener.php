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
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * @var \stevotvr\groupsub\operator\package_interface
	 */
	protected $pkg_operator;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * The name of the groupsub_groups table.
	 *
	 * @var string
	 */
	protected $group_table;

	/**
	 * The name of the groupsub_subs table.
	 *
	 * @var string
	 */
	protected $sub_table;

	/**
	 * @param \phpbb\config\config                          $config
	 * @param \phpbb\db\driver\driver_interface             $db
	 * @param \phpbb\controller\helper                      $helper
	 * @param \stevotvr\groupsub\operator\package_interface $pkg_operator
	 * @param \phpbb\template\template                      $template
	 * @param string                                        $group_table   The name of the
	 *                                                                     groupsub_groups table
	 * @param string                                        $sub_table     The name of the
	 *                                                                     groupsub_subs table
	 */
	public function __construct(config $config, driver_interface $db, helper $helper, package_interface $pkg_operator, template $template, $group_table, $sub_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->pkg_operator = $pkg_operator;
		$this->template = $template;
		$this->group_table = $group_table;
		$this->sub_table = $sub_table;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'				=> 'user_setup',
			'core.delete_group_after'		=> 'delete_group_after',
			'core.delete_user_after'		=> 'delete_user_after',
			'core.group_delete_user_after'	=> 'group_delete_user_after',
		);
	}

	/**
	 * Adds the extension language set and the controller link on user setup.
	 *
	 * @param \phpbb\event\data $event The event data
	 */
	public function user_setup(data $event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name'	=> 'stevotvr/groupsub',
			'lang_set'	=> 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;

		if ($this->groupsub_active() && $this->pkg_operator->count_packages())
		{
			$this->template->assign_var('U_GROUPSUB_SUBS', $this->helper->route('stevotvr_groupsub_main'));
		}
	}

	/**
	 * Remove references to a group after it is deleted.
	 *
	 * @param \phpbb\event\data	$event The event data
	 */
	public function delete_group_after(data $event)
	{
		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE group_id = ' . (int) $event['group_id'];
		$this->db->sql_query($sql);
	}

	/**
	 * Remove references to users after they are deleted.
	 *
	 * @param \phpbb\event\data	$event The event data
	 */
	public function delete_user_after(data $event)
	{
		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE ' . $this->db->sql_in_set('user_id', $event['user_ids']);
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->sub_table . '
				WHERE ' . $this->db->sql_in_set('user_id', $event['user_ids']);
		$this->db->sql_query($sql);
	}

	/**
	 * Remove references to users after they are deleted from a group.
	 *
	 * @param \phpbb\event\data	$event The event data
	 */
	public function group_delete_user_after(data $event)
	{
		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE group_id = ' . (int) $event['group_id'] . '
					AND ' . $this->db->sql_in_set('user_id', $event['user_id_ary']);
		$this->db->sql_query($sql);
	}

	/**
	 * Check if the extension is configured.
	 *
	 * @return boolean The extension is ready to use
	 */
	protected function groupsub_active()
	{
		$pp_sandbox = $this->config['stevotvr_groupsub_pp_sandbox'];
		$sb = $pp_sandbox && !empty($this->config['stevotvr_groupsub_pp_sb_business']);
		$live = !$pp_sandbox && !empty($this->config['stevotvr_groupsub_pp_business']);
		return $sb || $live;
	}
}
