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

use phpbb\config\config;
use phpbb\notification\manager;
use stevotvr\groupsub\entity\subscription_interface as entity;
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
	 * @param \phpbb\config\config        $config
	 * @param \phpbb\notification\manager $notification_manager
	 */
	public function setup(config $config, manager $notification_manager)
	{
		$this->notification_manager = $notification_manager;

		$this->warn_time = (int) $config['stevotvr_groupsub_warn_time'] * 86400;
		$this->grace = (int) $config['stevotvr_groupsub_grace'] * 86400;
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

	public function set_start($start)
	{
		$this->start = (int) $start;
		return $this;
	}

	public function set_limit($limit)
	{
		$this->limit = (int) $limit;
		return $this;
	}

	public function set_user($user_id)
	{
		$this->filters['s.user_id'] = (int) $user_id;
		return $this;
	}

	public function set_product($prod_id)
	{
		$this->filters['s.gs_id'] = (int) $prod_id;
		return $this;
	}

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

	public function get_subscriptions()
	{
		$where = array();
		foreach ($this->filters as $key => $value)
		{
			if (!$value)
			{
				continue;
			}

			$where[] = $key . ' = ' . $value;
		}

		return $this->get_subscription_rows(implode(' AND ', $where), $this->sort, $this->limit, $this->start);
	}

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
	 *                     product	string
	 *                     username	string
	 *                     entity	\stevotvr\groupsub\entity\subscription_interface
	 */
	protected function get_subscription_rows($where = null, $sort = null, $limit = 0, $start = 0)
	{
		$subscriptions = array();

		$sql_ary = array(
			'SELECT'	=> 's.*, p.gs_name, u.username',
			'FROM'		=> array($this->sub_table => 's'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->product_table => 'p'),
					'ON'	=> 's.gs_id = p.gs_id',
				),
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
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
		$result = $limit ? $this->db->sql_query_limit($sql, $limit, $start) : $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$subscriptions[] = array(
				'product'	=> $row['gs_name'],
				'username'	=> $row['username'],
				'entity'	=> $this->container->get('stevotvr.groupsub.entity.subscription')->import($row),
			);
		}
		$this->db->sql_freeresult($result);

		return $subscriptions;
	}

	public function count_subscriptions()
	{
		$sql = 'SELECT COUNT(sub_id) AS sub_count
				FROM ' . $this->sub_table;
		$result = $this->db->sql_query($sql);
		$count = $this->db->sql_fetchfield('sub_count');
		$this->db->sql_freeresult($result);

		return (int) $count;
	}

	public function add_subscription(entity $subscription)
	{
		$subscription->insert();
		$subscription_id = $subscription->get_id();
		$subscription->load($subscription_id);

		if ($subscription->get_id())
		{
			$groups = $this->get_groups($subscription->get_id());
			$this->add_user_to_groups($subscription->get_user(), $groups);
		}

		return $subscription;
	}

	public function create_subscription($prod_id, $user_id)
	{
		$product = $this->container->get('stevotvr.groupsub.entity.product')->load($prod_id);
		$length = $product->get_length() * 86400;

		$sql = 'SELECT sub_id, sub_expires
				FROM ' . $this->sub_table . '
				WHERE sub_active = 1 AND gs_id = ' . $product->get_id() . ' AND user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			$expires = (int) $row['sub_expires'] + $length;
			$sql = 'UPDATE ' . $this->sub_table . '
					SET sub_expires = ' . $expires . ', sub_notify_status = 0
					WHERE sub_id = ' . (int) $row['sub_id'];
			$this->db->sql_query($sql);

			$this->notification_manager->delete_notifications(array(
				'stevotvr.groupsub.notification.type.warn',
				'stevotvr.groupsub.notification.type.expired',
			), (int) $row['sub_id']);

			return;
		}

		$subscription = $this->container->get('stevotvr.groupsub.entity.subscription')
							->set_product($product->get_id())
							->set_user((int) $user_id)
							->set_expire(time() + $length);
		$this->add_subscription($subscription);
	}

	public function delete_subscription($sub_id)
	{
		$this->remove_user_from_groups($this->get_groups($sub_id));

		$sql = 'DELETE FROM ' . $this->sub_table . '
				WHERE sub_id = ' . (int) $sub_id;
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}

	public function get_subscribed_users($group_id)
	{
		$ids = array();

		$sql_ary = array(
			'SELECT'	=> 's.user_id',
			'FROM'		=> array($this->group_table => 'g'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->sub_table => 's'),
					'ON'	=> 'g.gs_id = s.gs_id',
				),
			),
			'WHERE'		=> 'g.group_id = ' . (int) $group_id . ' AND s.sub_expires > ' . (time() - $this->grace),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		return $ids;
	}

	public function process_expiring()
	{
		$sub_ids = array();
		$group_ids = array();

		$sql_ary = array(
			'SELECT'	=> 's.sub_id, s.user_id, g.group_id',
			'FROM'		=> array($this->sub_table => 's'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->group_table => 'g'),
					'ON'	=> 's.gs_id = g.gs_id',
				),
			),
			'WHERE'		=> 's.sub_active = 1 AND s.sub_expires < ' . (time() - $this->grace),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sub_ids[(int) $row['sub_id']] = true;
			$group_ids[(int) $row['user_id']][] = (int) $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		if (!count($sub_ids))
		{
			return;
		}

		foreach ($group_ids as $user => $groups)
		{
			$this->remove_user_from_groups($user, $groups);
		}

		$sql = 'UPDATE ' . $this->sub_table . '
				SET sub_active = 0
				WHERE ' . $this->db->sql_in_set('sub_id', array_keys($sub_ids));
		$this->db->sql_query($sql);
	}

	public function notify_subscribers()
	{
		$sub_ids = array();

		$sql_ary = array(
			'SELECT'	=> 's.sub_id, s.user_id, s.sub_expires, p.gs_ident, p.gs_name',
			'FROM'		=> array($this->sub_table => 's'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->product_table => 'p'),
					'ON'	=> 's.gs_id = p.gs_id',
				),
			),
			'WHERE'		=> 's.sub_notify_status < ' . subscription_interface::NOTIFY_EXPIRED . ' AND s.sub_expires < ' . time(),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sub_ids[] = (int) $row['sub_id'];
			$this->notification_manager->delete_notifications('stevotvr.groupsub.notification.type.warn', (int) $row['sub_id']);
			$this->notification_manager->add_notifications('stevotvr.groupsub.notification.type.expired', $row);
		}
		$this->db->sql_freeresult($result);

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

			$sql_ary['WHERE'] = 's.sub_notify_status < ' . subscription_interface::NOTIFY_WARN . ' AND s.sub_expires < ' . (time() + $this->warn_time);
			$sql = $this->db->sql_build_query('SELECT', $sql_ary);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sub_ids[] = (int) $row['sub_id'];
				$this->notification_manager->add_notifications('stevotvr.groupsub.notification.type.warn', $row);
			}
			$this->db->sql_freeresult($result);

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
	 * Get the groups associated with a subscription.
	 *
	 * @param int $sub_id The subscription ID
	 *
	 * @return array Array of group IDs
	 */
	protected function get_groups($sub_id)
	{
		$group_ids = array();

		$sql_ary = array(
			'SELECT'	=> 'g.group_id',
			'FROM'		=> array($this->sub_table => 's'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->group_table => 'g'),
					'ON'	=> 's.gs_id = g.gs_id',
				),
			),
			'WHERE'		=> 's.sub_id = ' . (int) $sub_id,
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_ids[] = (int) $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		return $group_ids;
	}

	/**
	 * Add a user to a list of groups.
	 *
	 * @param int   $user_id   The user ID
	 * @param array $group_ids Array of group IDs
	 */
	protected function add_user_to_groups($user_id, array $group_ids)
	{
		if (!function_exists('group_user_add'))
		{
			include $this->root_path . 'includes/functions_user.' . $this->php_ext;
		}

		foreach ($group_ids as $group_id)
		{
			group_user_add($group_id, $user_id);
		}
	}

	/**
	 * Remove a user from a list of groups.
	 *
	 * @param int   $user_id   The user ID
	 * @param array $group_ids Array of group IDs
	 */
	protected function remove_user_from_groups($user_id, array $group_ids)
	{
		if (!function_exists('group_user_del'))
		{
			include $this->root_path . 'includes/functions_user.' . $this->php_ext;
		}

		foreach ($group_ids as $group_id)
		{
			group_user_del($group_id, $user_id);
		}
	}

}
