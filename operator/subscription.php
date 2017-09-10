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

use stevotvr\groupsub\entity\subscription_interface as entity;

/**
 * Group Subscription subscription operator.
 */
class subscription extends operator implements subscription_interface
{
	public function get_subscriptions($product_id = 0)
	{
		$entities = array();

		$where = ($product_id > 0) ? 'WHERE gs_id = ' . (int) $product_id : '';
		$sql = 'SELECT *
				FROM ' . $this->sub_table . '
				' . $where;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$entities[] = $this->container->get('stevotvr.groupsub.entity.subscription')->import($row);
		}
		$this->db->sql_freeresult($result);

		return $entities;
	}

	public function add_subscription(entity $subscription)
	{
		$product->insert();
		$product_id = $product->get_id();
		return $product->load($product_id);
	}

	public function delete_subscription($sub_id)
	{
		$sql = 'DELETE FROM ' . $this->sub_table . '
				WHERE sub_id = ' . (int) $sub_id;
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}

	public function get_user_subscriptions($user_id)
	{
		$entities = array();

		$sql = 'SELECT *
				FROM ' . $this->sub_table . '
				WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$entities[] = $this->container->get('stevotvr.groupsub.entity.subscription')->import($row);
		}
		$this->db->sql_freeresult($result);

		return $entities;
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
			'WHERE'		=> 'g.group_id = ' . (int) $group_id,
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

}
