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

use phpbb\group\helper;
use stevotvr\groupsub\entity\product_interface as entity;

/**
 * Group Subscription product operator.
 */
class product extends operator implements product_interface
{
	/**
	 * @var \phpbb\group\helper
	 */
	protected $group_helper;

	/**
	 * Set up the operator.
	 *
	 * @param \phpbb\group\helper $group_helper
	 */
	public function setup(helper $group_helper)
	{
		$this->group_helper = $group_helper;
	}

	public function get_products($name = false)
	{
		$entities = array();

		$where = $name ? "WHERE gs_ident = '" . $this->db->sql_escape($name) . "'" : '';
		$sql = 'SELECT *
				FROM ' . $this->product_table . '
				' . $where . '
				ORDER BY gs_order ASC, gs_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$entities[] = $this->container->get('stevotvr.groupsub.entity.product')->import($row);
		}
		$this->db->sql_freeresult($result);

		return $entities;
	}

	public function count_products()
	{
		$sql = 'SELECT COUNT(gs_id) AS gs_count
				FROM ' . $this->product_table;
		$result = $this->db->sql_query($sql);
		$count = $this->db->sql_fetchfield('gs_count');
		$this->db->sql_freeresult($result);

		return (int) $count;
	}

	public function add_product(entity $product)
	{
		$product->insert();
		$product_id = $product->get_id();
		return $product->load($product_id);
	}

	public function delete_product($product_id)
	{
		$sql = 'DELETE FROM ' . $this->sub_table . '
				WHERE gs_id = ' . (int) $product_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE gs_id = ' . (int) $product_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->price_table . '
				WHERE gs_id = ' . (int) $product_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->product_table . '
				WHERE gs_id = ' . (int) $product_id;
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}

	public function move_product($product_id, $offset)
	{
		$ids = array();

		$sql = 'SELECT gs_id
				FROM ' . $this->product_table . '
				ORDER BY gs_order ASC, gs_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = $row['gs_id'];
		}
		$this->db->sql_freeresult($result);

		$position = array_search($product_id, $ids);
		array_splice($ids, $position, 1);
		$position += $offset;
		array_splice($ids, $position, 0, $product_id);

		foreach ($ids as $pos => $id)
		{
			$sql = 'UPDATE ' . $this->product_table . '
					SET gs_order = ' . $pos . '
					WHERE gs_id = ' . (int) $id;
			$this->db->sql_query($sql);
		}
	}

	public function get_prices($product_id = false)
	{
		$entities = array();

		$where = $product_id ? 'WHERE gs_id = ' . (int) $product_id : '';
		$sql = 'SELECT *
				FROM ' . $this->price_table . '
				' . $where . '
				ORDER BY p_order ASC, p_id ASC';
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$entities[(int) $row['gs_id']][] = $this->container->get('stevotvr.groupsub.entity.price')->import($row);
		}
		$this->db->sql_freeresult();

		return $entities;
	}

	public function set_prices($product_id, array $prices)
	{
		$sql = 'DELETE FROM ' . $this->price_table . '
				WHERE gs_id = ' . (int) $product_id;
		$this->db->sql_query($sql);

		$i = 0;
		foreach ($prices as $entity)
		{
			$entity->set_product($product_id)->set_order($i++)->insert();
		}
	}

	public function get_groups($product_id)
	{
		$ids = array();

		$sql = 'SELECT group_id
				FROM ' . $this->group_table . '
				WHERE gs_id = ' . (int) $product_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		return $ids;
	}

	public function get_all_groups()
	{
		$product_groups = array();

		$sql_ary = array(
			'SELECT'	=> 's.gs_id, g.group_id, g.group_name',
			'FROM'		=> array($this->group_table => 's'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(GROUPS_TABLE => 'g'),
					'ON'	=> 'g.group_id = s.group_id',
				),
			),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$product_groups[(int) $row['gs_id']][] = array(
				'id'	=> (int) $row['group_id'],
				'name'	=> $this->group_helper->get_name($row['group_name']),
			);
		}
		$this->db->sql_freeresult($result);

		foreach ($product_groups as &$product_group)
		{
			$names = array_map('strtolower', array_column($product_group, 'name'));
			array_multisort($names, SORT_ASC, SORT_STRING, $product_group);
		}

		return $product_groups;
	}

	public function add_group($product_id, $group_id)
	{
		$data = array(
			'gs_id'		=> (int) $product_id,
			'group_id'	=> (int) $group_id,
		);
		$sql = 'INSERT INTO ' . $this->group_table . '
				' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);
	}

	public function remove_group($product_id, $group_id)
	{
		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE gs_id = ' . (int) $product_id . '
					AND group_id = ' . (int) $group_id;
		$this->db->sql_query($sql);
	}

	public function remove_groups($product_id)
	{
		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE gs_id = ' . (int) $product_id;
		$this->db->sql_query($sql);
	}

	public function get_length($product_id, $price, $currency)
	{
		$sql = 'SELECT p_length
				FROM ' . $this->price_table . '
				WHERE gs_id = ' . (int) $product_id . '
					AND p_price = ' . (int) $price . '
					AND p_currency = ' . $this->db->sql_escape($currency);
		$this->db->sql_query($sql);
		$length = $this->db->sql_fetchfield('p_length');
		$this->db->sql_freeresult();

		return $length === false ? false : (int) $length;
	}
}
