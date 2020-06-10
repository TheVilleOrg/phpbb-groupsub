<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\controller;

use phpbb\auth\auth;
use phpbb\json_response;
use phpbb\pagination;
use phpbb\user;
use stevotvr\groupsub\entity\subscription_interface as sub_entity;
use stevotvr\groupsub\exception\base;
use stevotvr\groupsub\operator\package_interface as pkg_operator;
use stevotvr\groupsub\operator\subscription_interface as sub_operator;

/**
 * Group Subscription subscription management ACP controller.
 */
class acp_subs_controller extends acp_base_controller implements acp_subs_interface
{
	/**
	 * @var \stevotvr\groupsub\operator\package_interface
	 */
	protected $pkg_operator;

	/**
	 * @var \stevotvr\groupsub\operator\subscription_interface
	 */
	protected $sub_operator;

	/**
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var \phpbb\pagination
	 */
	protected $pagination;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * The name of the phpBB users table.
	 *
	 * @var string
	 */
	protected $phpbb_users_table;

	/**
	 * The root phpBB path.
	 *
	 * @var string
	 */
	protected $root_path;

	/**
	 * The script file extension.
	 *
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Admin root path.
	 *
	 * @var string
	 */
	protected $admin_path;

	/**
	 * The user ID for single user mode.
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * Set up the controller.
	 *
	 * @param \stevotvr\groupsub\operator\package_interface      $pkg_operator
	 * @param \stevotvr\groupsub\operator\subscription_interface $sub_operator
	 * @param \phpbb\auth\auth                                   $auth
	 * @param \phpbb\pagination                                  $pagination
	 * @param \phpbb\user                                        $user
	 * @param string                                             $phpbb_users_table The name of the phpBB users table
	 */
	public function setup(pkg_operator $pkg_operator, sub_operator $sub_operator, auth $auth, pagination $pagination, user $user, $phpbb_users_table)
	{
		$this->pkg_operator = $pkg_operator;
		$this->sub_operator = $sub_operator;
		$this->auth = $auth;
		$this->pagination = $pagination;
		$this->user = $user;
		$this->phpbb_users_table = $phpbb_users_table;
	}

	/**
	 * Set the phpBB installation path information.
	 *
	 * @param string $root_path         The root phpBB path
	 * @param string $php_ext           The script file extension
	 * @param string $adm_relative_path The relative admin root path
	 */
	public function set_path_info($root_path, $php_ext, $adm_relative_path)
	{
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->admin_path = $this->root_path . $adm_relative_path;
	}

	/**
	 * @inheritDoc
	 */
	public function add_lang()
	{
		parent::add_lang();

		$this->language->add_lang('acp_subscriptions', 'stevotvr/groupsub');
	}

	/**
	 * @inheritDoc
	 */
	public function set_user($user_id)
	{
		$this->user_id = $user_id;
		$this->u_action = append_sid($this->admin_path . 'index.' . $this->php_ext, 'i=users&amp;mode=groupsub&amp;u=' . $user_id);
		$this->template->assign_var('USER_ID', $user_id);
	}

	/**
	 * @inheritDoc
	 */
	public function display()
	{
		$this->add_lang();

		$sort_key = $sort_dir = '';
		$start = $limit = $pkg_id = 0;
		$params = $this->parse_display_params($sort_key, $sort_dir, $start, $limit, $pkg_id);

		if ($this->request->is_set_post('sort') || $this->request->is_set_post('filter'))
		{
			redirect($this->u_action . $params);
			return;
		}

		$this->load_sort_options($sort_key, $sort_dir);

		$subscriptions = $this->sub_operator
							->set_limit($limit)
							->set_start($start)
							->set_sort($this->get_sort_field($sort_key), ($sort_dir === 'd'))
							->set_package($pkg_id)
							->set_user($this->user_id)
							->get_subscriptions();

		$profile_url = append_sid($this->admin_path . 'index.' . $this->php_ext, 'i=users&amp;mode=overview');
		foreach ($subscriptions as $subscription)
		{
			$this->template->assign_block_vars('subscription', array(
				'S_PACKAGE_DELETED'	=> $subscription['package']['deleted'],
				'S_ACTIVE'			=> $subscription['entity']->is_active(),

				'USER'		=> get_username_string('full', $subscription['user_id'], $subscription['username'], $subscription['user_colour'], false, $profile_url),
				'PACKAGE'	=> $subscription['package']['name'],
				'STARTED'	=> $this->user->format_date($subscription['entity']->get_start()),
				'EXPIRES'	=> $subscription['entity']->get_expire() ? $this->user->format_date($subscription['entity']->get_expire()) : 0,

				'U_MOVE_UP'		=> $this->u_action . $params . '&amp;action=move_up&amp;id=' . $subscription['entity']->get_id(),
				'U_MOVE_DOWN'	=> $this->u_action . $params . '&amp;action=move_down&amp;id=' . $subscription['entity']->get_id(),
				'U_EDIT'		=> $this->u_action . $params . '&amp;action=edit&amp;id=' . $subscription['entity']->get_id(),
				'U_DELETE'		=> $this->u_action . $params . '&amp;action=delete&amp;id=' . $subscription['entity']->get_id(),
			));
		}

		$pkg_count = $this->load_packages($pkg_id);
		$this->template->assign_vars(array(
			'LIMIT'	=> $limit,

			'U_ACTION'	=> $this->u_action . $params,
			'U_ADD_SUB'	=> $this->u_action . $params . '&amp;action=add',

			'S_SHOW_ADD'	=> (bool) $pkg_count,
			'S_READ_ONLY'	=> !$this->auth->acl_get('a_groupsub_subscriptions_edit'),
			'S_SHOW_USER'	=> !$this->user_id,
		));

		$total = $this->sub_operator->count_subscriptions();
		$this->pagination->generate_template_pagination($this->u_action, 'pagination', 'start', $total, $limit, $start);
	}

	/**
	 * Load the sorting options into template variables.
	 *
	 * @param string $sort_key The current sort key value
	 * @param string $sort_dir The current sort direction value
	 */
	protected function load_sort_options($sort_key, $sort_dir)
	{
		$options = array(
			'u'	=> $this->language->lang('ACP_GROUPSUB_USER'),
			'p'	=> $this->language->lang('ACP_GROUPSUB_SUB'),
			's'	=> $this->language->lang('ACP_GROUPSUB_START'),
			'e'	=> $this->language->lang('ACP_GROUPSUB_EXPIRES'),
		);
		foreach ($options as $key => $name)
		{
			$this->template->assign_block_vars('sort_key', array(
				'KEY'	=> $key,
				'NAME'	=> $name,

				'S_SELECTED'	=> ($key === $sort_key),
			));
		}

		$options = array(
			'a'	=> $this->language->lang('ASCENDING'),
			'd'	=> $this->language->lang('DESCENDING'),
		);
		foreach ($options as $key => $name)
		{
			$this->template->assign_block_vars('sort_dir', array(
				'KEY'	=> $key,
				'NAME'	=> $name,

				'S_SELECTED'	=> ($key === $sort_dir),
			));
		}
	}

	/**
	 * Translate the sort key into the name of the database column.
	 *
	 * @param string $sort_key The sort key
	 *
	 * @return string The name of the database column
	 */
	protected function get_sort_field($sort_key)
	{
		switch ($sort_key)
		{
			case 'u':
				return 'u.username';
			break;
			case 'p':
				return 'p.pkg_name';
			break;
			case 'e':
				return 's.sub_expires';
			break;
		}

		return 's.sub_start';
	}

	/**
	 * @inheritDoc
	 */
	public function add()
	{
		$params = $this->parse_display_params();
		$entity = $this->container->get('stevotvr.groupsub.entity.subscription')->set_user($this->user_id);
		$this->add_edit_sub_data($entity, $params);

		$u_find_username = append_sid($this->root_path . 'memberlist.' . $this->php_ext, 'mode=searchuser&amp;form=add_edit_sub&amp;field=sub_user&amp;select_single=true');
		$this->template->assign_vars(array(
			'S_ADD_SUB'	=> true,

			'U_ACTION'			=> $this->u_action . $params . '&amp;action=add',
			'U_FIND_USERNAME'	=> $u_find_username,
		));
	}

	/**
	 * @inheritDoc
	 */
	public function edit($id)
	{
		$params = $this->parse_display_params();
		$subscription = $this->sub_operator->get_subscription($id);
		$profile_url = append_sid($this->admin_path . 'index.' . $this->php_ext, 'i=users&amp;mode=overview');
		$this->add_edit_sub_data($subscription['entity'], $params);

		$this->template->assign_vars(array(
			'S_EDIT_SUB'	=> true,
			'S_ACTIVE'		=> $subscription['entity']->is_active(),

			'SUB_PACKAGE'	=> $subscription['package']['name'],
			'SUB_USER'		=> get_username_string('full', $subscription['user_id'], $subscription['username'], $subscription['user_colour'], false, $profile_url),

			'U_ACTION'		=> $this->u_action . $params . '&amp;action=edit&amp;id=' . $id,

			'S_READ_ONLY'	=> !$this->auth->acl_get('a_groupsub_subscriptions_edit'),
		));
	}

	/**
	 * Process data for the add/edit subscription form.
	 *
	 * @param \stevotvr\groupsub\entity\subscription_interface $entity The subscription
	 * @param string                                           $params The URL parameters string
	 */
	protected function add_edit_sub_data(sub_entity $entity, $params)
	{
		$this->add_lang();

		$errors = array();

		$submit = $this->request->is_set_post('submit');

		add_form_key('add_edit_sub');

		$data = array(
			'user'		=> $this->request->variable('sub_user', '', true),
			'package'	=> $this->request->variable('sub_package', 0),
			'start'		=> $this->request->variable('sub_start', ''),
			'expire'	=> $this->request->variable('sub_expire', ''),
		);

		if (!$entity->get_id() && !$this->load_packages())
		{
			trigger_error($this->language->lang('ACP_GROUPSUB_ERROR_NO_PKGS') . adm_back_link($this->u_action . $params), E_USER_WARNING);
		}

		if ($submit && $this->auth->acl_get('a_groupsub_subscriptions_edit'))
		{
			if (!check_form_key('add_edit_sub'))
			{
				$errors[] = 'FORM_INVALID';
			}
			$parsed_data = array();

			if (!$entity->get_id() || $entity->is_active())
			{
				$parsed_data['start'] = $this->parse_date($data['start']);
				if (!$parsed_data['start'])
				{
					$errors[] = 'ACP_GROUPSUB_ERROR_INVALID_DATE';
				}

				$parsed_data['expire'] = $this->parse_date($data['expire']);
				if ($parsed_data['expire'] === false)
				{
					$errors[] = 'ACP_GROUPSUB_ERROR_INVALID_DATE';
				}
				else if (!empty($parsed_data['expire']) && $parsed_data['expire'] < time())
				{
					$errors[] = 'ACP_GROUPSUB_ERROR_DATE_IN_PAST';
				}
			}

			if (!$entity->get_id())
			{
				$parsed_data['user'] = isset($this->user_id) ? $this->user_id : $this->parse_username($data['user']);
				if (!$parsed_data['user'])
				{
					$errors[] = 'NO_USER';
				}

				$parsed_data['package'] = $data['package'];
			}

			foreach ($parsed_data as $name => $value)
			{
				try
				{
					$entity->{'set_' . $name}($value);
				}
				catch (base $e)
				{
					$errors[] = $e->get_message($this->language);
				}
			}

			if (empty($errors) && !$entity->get_id())
			{
				$conflict_id = $this->sub_operator->get_conflict($entity);
				if ($conflict_id)
				{
					trigger_error($this->language->lang('ACP_GROUPSUB_ERROR_SUB_CONFLICT', $this->u_action . '&amp;action=edit&amp;id=' . $conflict_id), E_USER_WARNING);
				}
			}

			if (empty($errors))
			{
				if ($entity->get_id())
				{
					$entity->save();
					$message = 'ACP_GROUPSUB_SUB_EDIT_SUCCESS';
				}
				else
				{
					$entity = $this->sub_operator->add_subscription($entity);
					$message = 'ACP_GROUPSUB_SUB_ADD_SUCCESS';
				}

				trigger_error($this->language->lang($message) . adm_back_link($this->u_action . $params));
			}
		}

		$errors = array_map(array($this->language, 'lang'), $errors);

		$this->template->assign_vars(array(
			'ERROR_MESSAGE'	=> implode('<br>', $errors),

			'U_BACK'	=> $this->u_action . $params,
		));

		$this->assign_tpl_vars($entity, $data);
	}

	/**
	 * Assign the main template variables.
	 *
	 * @param \stevotvr\groupsub\entity\subscription_interface $entity The package
	 * @param array                                            $post   The posted data
	 */
	protected function assign_tpl_vars(sub_entity $entity, array $post)
	{
		$posted = $this->request->is_set_post('submit');

		$user = !$entity->get_id() ? $post['user'] : $entity->get_user();
		$package = !$entity->get_id() ? $post['package'] : $entity->get_package();

		if ($posted)
		{
			$start = $post['start'];
			$expire = $post['expire'];
		}
		else if ($entity->get_id())
		{
			$start = $this->user->format_date($entity->get_start(), 'Y-m-d');
			$expire = $entity->get_expire() ? $this->user->format_date($entity->get_expire(), 'Y-m-d') : '';
		}
		else
		{
			$start = $this->user->format_date(time(), 'Y-m-d');
			$expire = '';
		}

		$this->template->assign_vars(array(
			'SUB_USER'		=> $user,
			'SUB_PACKAGE'	=> $package,
			'SUB_START'		=> $start,
			'SUB_EXPIRE'	=> $expire,

			'S_SHOW_USER'	=> !$this->user_id,
		));
	}

	/**
	 * Parse the user field for creating a subscription.
	 *
	 * @param string $username The username
	 * @return int|boolean The user ID, or false if not found
	 */
	protected function parse_username($username)
	{
		$sql = 'SELECT user_id
				FROM ' . $this->phpbb_users_table . "
				WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
		$this->db->sql_query($sql);
		$userrow = $this->db->sql_fetchrow();
		$this->db->sql_freeresult();

		if (!$userrow)
		{
			return false;
		}

		return (int) $userrow['user_id'];
	}

	/**
	 * Parse a date field.
	 *
	 * @param string $input The formatted date
	 * @return int|boolean The Unix timestamp, or false if invalid
	 */
	protected function parse_date($input)
	{
		$input = trim($input);
		if ($input === '')
		{
			return 0;
		}

		if (preg_match('/^(\d{4})\-(\d{2})\-(\d{2})$/', $input, $date_parts))
		{
			return $this->user->create_datetime()
								->setDate((int) $date_parts[1], (int) $date_parts[2], (int) $date_parts[3])
								->setTime(0, 0, 0)
								->getTimestamp();
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function delete($id)
	{
		if (!$this->auth->acl_get('a_groupsub_subscriptions_edit'))
		{
			return;
		}

		$this->add_lang();

		$sort_key = $sort_dir = '';
		$start = $limit = $pkg_id = 0;
		$params = $this->parse_display_params($sort_key, $sort_dir, $start, $limit, $pkg_id);

		if (!confirm_box(true))
		{
			$hidden_fields = build_hidden_fields(array(
				'id'		=> $id,
				'mode'		=> $this->request->variable('mode', ''),
				'sk'		=> $sort_key,
				'sd'		=> $sort_dir,
				'start'		=> $start,
				'limit'		=> $limit,
				'pkg_id'	=> $pkg_id,
				'action'	=> 'delete',
			));
			confirm_box(false, $this->language->lang('ACP_GROUPSUB_SUB_DELETE_CONFIRM'), $hidden_fields);
			return;
		}

		$this->sub_operator->delete_subscription($id);

		if ($this->request->is_ajax())
		{
			$json_response = new json_response();
			$json_response->send(array(
				'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
				'MESSAGE_TEXT'	=> $this->language->lang('ACP_GROUPSUB_SUB_DELETE_SUCCESS'),
				'REFRESH_DATA'	=> array(
					'time'	=> 3
				),
			));
		}

		trigger_error($this->language->lang('ACP_GROUPSUB_SUB_DELETE_SUCCESS') . adm_back_link($this->u_action . $params));
	}

	/**
	 * Parse the URL parameters for the main list display options.
	 *
	 * @param string &$sort_key   Variable to hold the value of the sort key parameters
	 * @param string &$sort_dir   Variable to hold the value of the sort direction parameters
	 * @param int    &$start      Variable to hold the value of the start parameters
	 * @param int    &$limit      Variable to hold the value of the limit parameters
	 * @param int    &$package_id Variable to hold the value of the package parameters
	 *
	 * @return string The reconstructed parameter string
	 */
	protected function parse_display_params(&$sort_key = '', &$sort_dir = '', &$start = 0, &$limit = 0, &$package_id = 0)
	{
		$sort_key = $this->request->variable('sk', 's');
		$sort_dir = $this->request->variable('sd', 'd');
		$start = $this->request->variable('start', 0);
		$limit = min(100, $this->request->variable('limit', (int) $this->config['topics_per_page']));
		$package_id = $this->request->variable('pkg_id', 0);

		return sprintf(
			'&amp;sk=%s&amp;sd=%s&amp;start=%d&amp;limit=%d&amp;pkg_id=%d',
			$sort_key,
			$sort_dir,
			$start,
			$limit,
			$package_id
		);
	}

	/**
	 * Load the list of available packages into template block variables.
	 *
	 * @return int The number of packages
	 */
	protected function load_packages()
	{
		$packages = $this->pkg_operator->get_package_list();

		foreach ($packages as $id => $name)
		{
			$this->template->assign_block_vars('package', array(
				'ID'	=> $id,
				'NAME'	=> $name,
			));
		}

		return count($packages);
	}
}
