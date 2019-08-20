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
use stevotvr\groupsub\exception\out_of_bounds;
use stevotvr\groupsub\operator\package as pkg;

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
	 * @param \phpbb\notification\manager       $notification_manager
	 * @param \phpbb\event\dispatcher_interface $phpbb_dispatcher
	 * @param string                            $phpbb_users_table    The name of the phpBB users table
	 */
	public function setup(manager $notification_manager, dispatcher_interface $phpbb_dispatcher, $phpbb_users_table)
	{
		$this->notification_manager = $notification_manager;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
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
			'SELECT'	=> 's.*, p.pkg_name, p.pkg_deleted, u.username',
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
				'username'	=> $row['username'],
				'entity'	=> $this->container->get('stevotvr.groupsub.entity.subscription')->import($row),
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

			$this->add_user_to_groups($user_id, $sub_id, $package_id);

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
				'sub_expires'		=> (int) $row['sub_expires'] + $length,
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

			$this->remove_user_from_groups($user_id, $row['sub_id']);
			$this->add_user_to_groups($user_id, $row['sub_id'], $term->get_package());

			return (int) $row['sub_id'];
		}

		$subscription = $this->container->get('stevotvr.groupsub.entity.subscription')
							->set_package($term->get_package())
							->set_user((int) $user_id)
							->set_start(time())
							->set_expire(time() + $length);
		return $this->add_subscription($subscription);
	}

	/**
	 * @inheritDoc
	 */
	public function delete_subscription($sub_id)
	{
		$sql = 'SELECT pkg_id, user_id
				FROM ' . $this->sub_table . '
				WHERE sub_active <> 0 AND sub_id = ' . (int) $sub_id;
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
	}

	/**
	 * @inheritDoc
	 */
	public function process_expiring()
	{
		$sub_ids = array();

		$sql = 'SELECT sub_id, pkg_id, user_id
				FROM ' . $this->sub_table . '
				WHERE sub_active = 1
					AND sub_expires <> 0
					AND sub_expires < ' . (time() - $this->grace);
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
		}
	}

	/**
	 * @inheritDoc
	 */
	public function notify_subscribers()
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
			'WHERE'		=> 's.sub_notify_status < ' . subscription_interface::NOTIFY_EXPIRED . '
								AND s.sub_active <> 0
								AND s.sub_expires <> 0
								AND s.sub_expires < ' . time(),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult();
		foreach ($rows as $row)
		{
			$sub_ids[] = (int) $row['sub_id'];
			$this->notification_manager->delete_notifications('stevotvr.groupsub.notification.type.warn', (int) $row['sub_id']);
			$this->notification_manager->add_notifications('stevotvr.groupsub.notification.type.expired', $row);
		}

		if (count($sub_ids))
		{
			$sql = 'UPDATE ' . $this->sub_table . '
					SET sub_notify_status = ' . subscription_interface::NOTIFY_EXPIRED . '
					WHERE ' . $this->db->sql_in_set('sub_id', $sub_ids);
			$this->db->sql_query($sql);
		}

		if ($this->warn_time)
		{
			$sub_ids = array();

			$sql_ary['WHERE'] = 's.sub_notify_status < ' . subscription_interface::NOTIFY_WARN . '
									AND s.sub_active <> 0
									AND s.sub_expires <> 0
									AND s.sub_expires < ' . (time() + $this->warn_time);
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
						SET sub_notify_status = ' . subscription_interface::NOTIFY_WARN . '
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
		$this->remove_user_from_groups($user_id, $sub_id);

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
	 * Add a user to the subscribed groups.
	 *
	 * @param int $user_id The user ID
	 * @param int $sub_id  The subscription ID
	 * @param int $pkg_id  The package ID
	 */
	protected function add_user_to_groups($user_id, $sub_id, $pkg_id)
	{
		if (!function_exists('group_user_add'))
		{
			include $this->root_path . 'includes/functions_user.' . $this->php_ext;
		}

		$sql = 'SELECT group_id, group_default, group_type
				FROM ' . $this->group_table . '
				WHERE pkg_id = ' . (int) $pkg_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$data = array(
				'sub_id'		=> $sub_id,
				'user_id'		=> $user_id,
				'group_id'		=> $row['group_id'],
				'group_type'	=> $row['group_type'],
			);

			if ($row['group_type'] == pkg::GROUP_TYPE_PERMANENT)
			{
				group_user_add($row['group_id'], $user_id, false, false, (bool) $row['group_default']);
			}
			else if ($row['group_type'] == pkg::GROUP_TYPE_SUBSCRIPTION)
			{
				$sql = 'INSERT INTO ' . $this->group_table . '
						' . $this->db->sql_build_array('INSERT', $data);
				$this->db->sql_query($sql);

				group_user_add($row['group_id'], $user_id, false, false, (bool) $row['group_default']);
			}
			else if ($row['group_type'] == pkg::GROUP_TYPE_NEWLY_REGISTERED)
			{
				group_user_del($row['group_id'], $user_id);
			}
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Remove a user from the subscribed groups.
	 *
	 * @param int $user_id The user ID
	 * @param int $sub_id  The subscription ID
	 */
	protected function remove_user_from_groups($user_id, $sub_id)
	{
		$sub_groups = array();

		$sql = 'SELECT group_id
				FROM ' . $this->group_table . '
				WHERE sub_id = ' . (int) $sub_id . '
					AND group_type = ' . (int) pkg::GROUP_TYPE_SUBSCRIPTION;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sub_groups[] = (int) $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($sub_groups))
		{
			return;
		}

		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE sub_id = ' . (int) $sub_id;
		$this->db->sql_query($sql);

		$keep_groups = array();
		$sql = 'SELECT group_id
				FROM ' . $this->group_table . '
				WHERE user_id = ' . (int) $user_id . '
					AND sub_id <> ' . (int) $sub_id . '
					AND group_type = ' . (int) pkg::GROUP_TYPE_SUBSCRIPTION . '
					AND ' . $this->db->sql_in_set('group_id', $sub_groups);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$keep_groups[] = (int) $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		if (!function_exists('group_user_del'))
		{
			include $this->root_path . 'includes/functions_user.' . $this->php_ext;
		}

		foreach (array_diff($sub_groups, $keep_groups) as $group_id)
		{
			group_user_del($group_id, $user_id);
		}
	}

}
