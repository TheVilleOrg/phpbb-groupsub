<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\operator;

use phpbb\event\dispatcher_interface;
use phpbb\notification\manager;
use stevotvr\groupsub\entity\subscription_interface as entity;
use stevotvr\groupsub\entity\term_interface as term_entity;
use stevotvr\groupsub\operator\package_interface as pkg_operator;
use stevotvr\groupsub\exception\out_of_bounds;

/**
 * Group Subscription subscription operator.
 */
class subscription extends operator implements subscription_interface
{
	/**
	 * @var \phpbb\notification\manager
	 */
	protected $notification_manager;

	/**
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $phpbb_dispatcher;

	/**
	 * @var \stevotvr\groupsub\operator\package_interface
	 */
	protected $pkg_operator;

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
	 * The offset for querying subscriptions.
	 *
	 * @var int
	 */
	protected $start = 0;

	/**
	 * The limit for querying subscriptions.
	 *
	 * @var int
	 */
	protected $limit = 0;

	/**
	 * The list of filters for building the WHERE clause.
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * The ORDER BY clause.
	 *
	 * @var string
	 */
	protected $sort = null;

	/**
	 * The warning time in seconds.
	 *
	 * @var int
	 */
	protected $warn_time;

	/**
	 * The grace period in seconds.
	 *
	 * @var int
	 */
	protected $grace;

	/**
	 * Set up the operator.
	 *
	 * @param \phpbb\notification\manager                   $notification_manager
	 * @param \phpbb\event\dispatcher_interface             $phpbb_dispatcher
	 * @param \stevotvr\groupsub\operator\package_interface $pkg_operator
	 * @param string                                        $phpbb_users_table    The name of the phpBB users table
	 */
	public function setup(manager $notification_manager, dispatcher_interface $phpbb_dispatcher, pkg_operator $pkg_operator, $phpbb_users_table)
	{
		$this->notification_manager = $notification_manager;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->pkg_operator = $pkg_operator;
		$this->phpbb_users_table = $phpbb_users_table;

		$this->warn_time = (int) $this->config['stevotvr_groupsub_warn_time'] * 86400;
		$this->grace = (int) $this->config['stevotvr_groupsub_grace'] * 86400;
	}

	/**
	 * Set the phpBB installation path information.
	 *
	 * @param string $root_path The root phpBB path
	 * @param string $php_ext   The script file extension
	 */
	public function set_path_info($root_path, $php_ext)
	{
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @inheritDoc
	 */
	public function set_start($start)
	{
		$this->start = (int) $start;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function set_limit($limit)
	{
		$this->limit = (int) $limit;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function set_user($user_id)
	{
		$this->filters['s.user_id'] = (int) $user_id;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function set_package($package_id)
	{
		$this->filters['s.pkg_id'] = (int) $package_id;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function set_active($active)
	{
		$this->filters['s.sub_active'] = $active;
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function set_sort($field, $desc = false)
	{
		if (!$field)
		{
			$this->sort = null;
			return $this;
		}

		$this->sort = $field . ($desc ? ' DESC' : ' ASC');
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function get_subscriptions()
	{
		$where = array();
		foreach ($this->filters as $key => $value)
		{
			if (!is_bool($value) && !$value)
			{
				continue;
			}

			$where[] = $key . ' = ' . (int) $value;
		}

		return $this->get_subscription_rows(implode(' AND ', $where), $this->sort, $this->limit, $this->start);
	}

	/**
	 * @inheritDoc
	 */
	public function get_subscription($sub_id)
	{
		$subscriptions = $this->get_subscription_rows('s.sub_id = ' . (int) $sub_id);

		if (!count($subscriptions))
		{
			throw new out_of_bounds('sub_id');
		}

		return $subscriptions[0];
	}

	/**
	 * Get subscription data from the database.
	 *
	 * @param string|null $where The WHERE clause
	 * @param string|null $sort  The ORDER BY clause
	 * @param int         $limit The maximum number of rows to get
	 * @param int         $start The row at which to start
	 *
	 * @return array Array of subscription data
	 *                     package	string
	 *                     username	string
	 *                     entity	\stevotvr\groupsub\entity\subscription_interface
	 */
	protected function get_subscription_rows($where = null, $sort = null, $limit = 0, $start = 0)
	{
		$subscriptions = array();

		$sql_ary = array(
			'SELECT'	=> 's.*, p.pkg_name, p.pkg_deleted, u.username, u.user_colour',
			'FROM'		=> array($this->sub_table => 's'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->package_table => 'p'),
					'ON'	=> 's.pkg_id = p.pkg_id',
				),
				array(
					'FROM'	=> array($this->phpbb_users_table => 'u'),
					'ON'	=> 's.user_id = u.user_id',
				),
			),
		);

		if ($where)
		{
			$sql_ary['WHERE'] = $where;
		}

		if ($sort)
		{
			$sql_ary['ORDER_BY'] = $sort;
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$limit ? $this->db->sql_query_limit($sql, $limit, $start) : $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$subscriptions[] = array(
				'package'	=> array(
					'name'		=> $row['pkg_name'],
					'deleted'	=> (bool) $row['pkg_deleted'],
				),
				'username'		=> $row['username'],
				'user_id'		=> $row['user_id'],
				'user_colour'	=> $row['user_colour'],
				'entity'		=> $this->container->get('stevotvr.groupsub.entity.subscription')->import($row),
			);
		}
		$this->db->sql_freeresult();

		return $subscriptions;
	}

	/**
	 * @inheritDoc
	 */
	public function count_subscriptions()
	{
		$sql = 'SELECT COUNT(sub_id) AS sub_count
				FROM ' . $this->sub_table;
		$this->db->sql_query($sql);
		$count = $this->db->sql_fetchfield('sub_count');
		$this->db->sql_freeresult();

		return (int) $count;
	}

	/**
	 * @inheritDoc
	 */
	public function add_subscription(entity $subscription)
	{
		$subscription->insert();
		$subscription_id = $subscription->get_id();
		$subscription->load($subscription_id);

		if ($subscription->get_id())
		{
			$user_id = $subscription->get_user();
			$sub_id = $subscription->get_id();
			$package_id = $subscription->get_package();

			$this->execute_actions($user_id, $package_id);

			/**
			 * Event triggered when a subscription is started.
			 *
			 * @event stevotvr.groupsub.subscription_started
			 * @var int user_id    The user ID
			 * @var int sub_id     The subscription ID
			 * @var int package_id The package ID
			 * @since 0.1.0
			 */
			$vars = array('user_id', 'sub_id', 'package_id');
			extract($this->phpbb_dispatcher->trigger_event('stevotvr.groupsub.subscription_started', compact($vars)));

			$sql = 'SELECT pkg_ident, pkg_name
					FROM ' . $this->package_table . '
					WHERE pkg_id = ' . (int) $subscription->get_package();
			$this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow();
			$this->db->sql_freeresult();

			$row['sub_id'] = $subscription->get_id();
			$row['user_id'] = $subscription->get_user();

			$this->notification_manager->add_notifications('stevotvr.groupsub.notification.type.started', $row);

			if ($this->config['stevotvr_groupsub_notify_admins'])
			{
				$this->notification_manager->add_notifications('stevotvr.groupsub.notification.type.admin_started', $row);
			}
		}

		return $subscription->get_id();
	}

	/**
	 * @inheritDoc
	 */
	public function create_subscription(term_entity $term, $user_id)
	{
		$length = $term->get_length() * 86400;

		$sql = 'SELECT sub_id, sub_expires
				FROM ' . $this->sub_table . '
				WHERE sub_active = 1 AND pkg_id = ' . (int) $term->get_package() . ' AND user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult();

		if ($row)
		{
			$data = array(
				'sub_expires'		=> $length > 0 ? (int) $row['sub_expires'] + $length : 0,
				'sub_notify_status'	=> 0,
			);
			$sql = 'UPDATE ' . $this->sub_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $data) . '
					WHERE sub_id = ' . (int) $row['sub_id'];
			$this->db->sql_query($sql);

			$this->notification_manager->delete_notifications(array(
				'stevotvr.groupsub.notification.type.warn',
				'stevotvr.groupsub.notification.type.expired',
			), (int) $row['sub_id']);

			$this->execute_actions($user_id, $term->get_package());

			return (int) $row['sub_id'];
		}

		$subscription = $this->container->get('stevotvr.groupsub.entity.subscription')
							->set_package($term->get_package())
							->set_user((int) $user_id)
							->set_start(time())
							->set_expire($length > 0 ? time() + $length : 0);
		return $this->add_subscription($subscription);
	}

	/**
	 * @inheritDoc
	 */
	public function delete_subscription($sub_id)
	{
		$sql_ary = array(
			'SELECT'	=> 's.sub_id, s.pkg_id, s.user_id, p.pkg_ident, p.pkg_name',
			'FROM'		=> array($this->sub_table => 's'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->package_table => 'p'),
					'ON'	=> 's.pkg_id = p.pkg_id',
				),
			),
			'WHERE'		=> 's.sub_active <> 0
								AND s.sub_id = ' . (int) $sub_id,
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult();

		if (!$row)
		{
			return;
		}

		$sql = 'UPDATE ' . $this->sub_table . '
				SET sub_active = 0
				WHERE sub_id = ' . (int) $sub_id;
		$this->db->sql_query($sql);

		$this->end_subscription((int) $row['user_id'], $sub_id, (int) $row['pkg_id']);

		$row['cancelled'] = true;
		$this->notification_manager->add_notifications('stevotvr.groupsub.notification.type.expired', $row);
	}

	/**
	 * @inheritDoc
	 */
	public function process_expiring()
	{
		$sub_ids = array();

		$sql_ary = array(
			'SELECT'	=> 's.sub_id, s.pkg_id, s.user_id, p.pkg_ident, p.pkg_name',
			'FROM'		=> array($this->sub_table => 's'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->package_table => 'p'),
					'ON'	=> 's.pkg_id = p.pkg_id',
				),
			),
			'WHERE'		=> 's.sub_active = 1
								AND s.sub_expires <> 0
								AND s.sub_expires < ' . (time() - $this->grace),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult();
		foreach ($rows as $row)
		{
			$sub_ids[] = $row['sub_id'];
		}

		if (empty($sub_ids))
		{
			return;
		}

		$sql = 'UPDATE ' . $this->sub_table . '
				SET sub_active = 0
				WHERE ' . $this->db->sql_in_set('sub_id', $sub_ids);
		$this->db->sql_query($sql);

		foreach ($rows as $row)
		{
			$this->end_subscription($row['user_id'], $row['sub_id'], $row['pkg_id']);

			$this->notification_manager->add_notifications('stevotvr.groupsub.notification.type.expired', $row);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function notify_subscribers()
	{
		if ($this->warn_time)
		{
			$sub_ids = array();

			$sql_ary = array(
				'SELECT'	=> 's.sub_id, s.user_id, s.sub_expires, p.pkg_ident, p.pkg_name',
				'FROM'		=> array($this->sub_table => 's'),
				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array($this->package_table => 'p'),
						'ON'	=> 's.pkg_id = p.pkg_id',
					),
				),
				'WHERE'		=> 's.sub_notify_status = 0
									AND s.sub_active <> 0
									AND s.sub_expires <> 0
									AND s.sub_expires < ' . (time() + $this->warn_time),
			);
			$sql = $this->db->sql_build_query('SELECT', $sql_ary);
			$this->db->sql_query($sql);
			$rows = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult();
			foreach ($rows as $row)
			{
				$sub_ids[] = (int) $row['sub_id'];
				$this->notification_manager->add_notifications('stevotvr.groupsub.notification.type.warn', $row);
			}

			if (count($sub_ids))
			{
				$sql = 'UPDATE ' . $this->sub_table . '
						SET sub_notify_status = 1
						WHERE ' . $this->db->sql_in_set('sub_id', $sub_ids);
				$this->db->sql_query($sql);
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_conflict(entity $subscription)
	{
		$sql = 'SELECT sub_id
				FROM ' . $this->sub_table . '
				WHERE sub_active = 1
					AND pkg_id = ' . (int) $subscription->get_package() . '
					AND user_id = ' . (int) $subscription->get_user();
		$this->db->sql_query($sql);
		$sub_id = $this->db->sql_fetchfield('sub_id');
		$this->db->sql_freeresult();

		return $sub_id ? (int) $sub_id : false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_user_subscriptions($user_id)
	{
		$subscriptions = array();

		$sql = 'SELECT *
				FROM ' . $this->sub_table . '
				WHERE sub_active = 1
					AND user_id = ' . (int) $user_id . '
				ORDER BY sub_expires ASC';
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$subscriptions[(int) $row['pkg_id']] = $this->container->get('stevotvr.groupsub.entity.subscription')->import($row);
		}
		$this->db->sql_freeresult();

		return $subscriptions;
	}

	/**
	 * End an active subscription.
	 *
	 * @param int $user_id    The user ID
	 * @param int $sub_id     The subscription ID
	 * @param int $package_id The package ID
	 */
	protected function end_subscription($user_id, $sub_id, $package_id)
	{
		$this->execute_actions($user_id, $package_id, true);

		/**
		 * Event triggered when a subscription is ended.
		 *
		 * @event stevotvr.groupsub.subscription_ended
		 * @var int user_id    The user ID
		 * @var int sub_id     The subscription ID
		 * @var int package_id The package ID
		 * @since 0.1.0
		 */
		$vars = array('user_id', 'sub_id', 'package_id');
		extract($this->phpbb_dispatcher->trigger_event('stevotvr.groupsub.subscription_ended', compact($vars)));
	}

	/**
	 * Execute the subscription actions for a user.
	 *
	 * @param int  $user_id The user ID
	 * @param int  $pkg_id  The package ID
	 * @param bool $end     True to execute the end actions, false to execute the start actions
	 */
	protected function execute_actions($user_id, $pkg_id, $end = false)
	{
		$actions = $end ? $this->pkg_operator->get_end_actions($pkg_id) : $this->pkg_operator->get_start_actions($pkg_id);

		$groups_add = array();
		$groups_remove = array();
		$groups_default = array();
		$custom = array();

		foreach ($actions as $action)
		{
			switch ($action['name'])
			{
				case 'gs_add_group':
					$group_id = (int) $action['param'];
					if (!isset($groups_add[$group_id]))
					{
						$groups_add[$group_id] = true;
						unset($groups_remove[$group_id]);
					}
				break;
				case 'gs_remove_group':
					$group_id = (int) $action['param'];
					if (!isset($groups_add[$group_id]))
					{
						$groups_remove[$group_id] = true;
					}
				break;
				case 'gs_default_group':
					$group_id = (int) $action['param'];
					$groups_default[$group_id] = true;
				break;
				default:
					$custom[] = $action;
			}
		}

		if (!empty($groups_add))
		{
			if (!function_exists('group_user_add'))
			{
				include $this->root_path . 'includes/functions_user.' . $this->php_ext;
			}

			foreach (array_keys($groups_add) as $group_id)
			{
				group_user_add($group_id, $user_id);
			}
		}

		if (!empty($groups_remove))
		{
			$sql_ary = array(
				'SELECT'	=> 'a.act_param',
				'FROM'		=> array($this->sub_table => 's'),
				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array($this->action_table => 'a'),
						'ON'	=> 's.pkg_id = a.pkg_id',
					),
				),
				'WHERE'		=> 's.sub_active = 1
									AND s.user_id = ' . $user_id . '
									AND a.pkg_id <> ' . (int) $pkg_id . '
									AND ' . $this->db->sql_in_set('a.act_name', array('gs_add_group', 'gs_default_group')),
			);
			$sql = $this->db->sql_build_query('SELECT', $sql_ary);
			$this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow())
			{
				unset($groups_remove[(int) $row['act_param']]);
			}
			$this->db->sql_freeresult();

			$groups_remove = array_keys($groups_remove);
		}

		if (!empty($groups_remove))
		{
			if (!function_exists('group_user_del'))
			{
				include $this->root_path . 'includes/functions_user.' . $this->php_ext;
			}

			foreach ($groups_remove as $group_id)
			{
				group_user_del($group_id, $user_id);
			}
		}

		if (!empty($groups_default))
		{
			if (!function_exists('group_set_user_default'))
			{
				include $this->root_path . 'includes/functions_user.' . $this->php_ext;
			}

			foreach (array_keys($groups_default) as $group_id)
			{
				group_set_user_default($group_id, array($user_id));
			}
		}

		foreach ($custom as $action)
		{
			$param = $action['param'];
			$action = $action['name'];

			/**
			 * Event triggered when a custom subscription action is executed.
			 *
			 * @event stevotvr.groupsub.action
			 * @var int    user_id The user ID
			 * @var string action  The name of the action to execute
			 * @var string param   The parameter for the action
			 * @since 1.2.0
			 */
			$vars = array('user_id', 'action', 'param');
			extract($this->phpbb_dispatcher->trigger_event('stevotvr.groupsub.action', compact($vars)));
		}
	}
}
