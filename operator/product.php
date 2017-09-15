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

use stevotvr\groupsub\entity\product_interface as entity;

/**
 * Group Subscription product operator.
 */
class product extends operator implements product_interface
{
	public function get_products()
	{
		$entities = array();

		$sql = 'SELECT *
				FROM ' . $this->product_table . '
				ORDER BY gs_order ASC, gs_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$entities[] = $this->container->get('stevotvr.groupsub.entity.product')->import($row);
		}
		$this->db->sql_freeresult($result);

		return $entities;
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
}
