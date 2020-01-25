<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\notification\type;

use phpbb\config\config;

/**
 * Group Subscription base admin notification.
 */
abstract class admin_base_type extends base_type
{
	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @param \phpbb\config\config $config
	 */
	public function set_config(config $config)
	{
		$this->config = $config;
	}

	/**
	* {@inheritdoc}
	*/
	public function is_available()
	{
		return $this->config['stevotvr_groupsub_notify_admins'] && $this->auth->acl_get('a_board');
	}

	/**
	 * @inheritDoc
	 */
	public function find_users_for_notification($data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'	=> array(),
		), $options);

		$admin_ary = $this->auth->acl_get_list(false, 'a_board', false);
		$users = (!empty($admin_ary[0]['a_board'])) ? $admin_ary[0]['a_board'] : array();

		$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE user_type = ' . USER_FOUNDER;
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$users[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult();

		if (empty($users))
		{
			return array();
		}

		$users = array_unique($users);

		return $this->check_user_notification_options($users, $options);
	}

	/**
	 * @inheritDoc
	 */
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'adm/index.' . $this->php_ext ,
			'i=-stevotvr-groupsub-acp-main_module&mode=subscriptions&action=edit&id=' . $this->get_data('sub_id'),
			true, $this->user->session_id);
	}

	/**
	 * @inheritDoc
	 */
	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('sub_id', $data['sub_id']);
		$this->set_data('sub_user', $data['user_id']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
