<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\notification\type;

use phpbb\controller\helper;
use phpbb\notification\type\base;
use phpbb\user_loader;

/**
 * Group Subscription base notification.
 */
abstract class base_type extends base
{
	/**
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * @var \phpbb\user_loader
	 */
	protected $user_loader;

	/**
	 * Set up the notification type.
	 *
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbb\user_loader       $user_loader
	 */
	public function setup(helper $helper, user_loader $user_loader)
	{
		$this->helper = $helper;
		$this->user_loader = $user_loader;

		$this->language->add_lang('notifications', 'stevotvr/groupsub');
	}

	static public function get_item_id($data)
	{
		return (int) $data['sub_id'];
	}

	static public function get_item_parent_id($data)
	{
		return 0;
	}

	public function find_users_for_notification($data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'	=> array(),
		), $options);

		$this->user_loader->load_users((array) $data['user_id']);

		return $this->check_user_notification_options((array) $data['user_id'], $options);
	}

	public function users_to_query()
	{
		return array();
	}

	public function get_url()
	{
		return $this->helper->route('stevotvr_groupsub_main', array('name' => $this->get_data('pkg_ident')));
	}

	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('pkg_ident', $data['pkg_ident']);
		$this->set_data('pkg_name', $data['pkg_name']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
