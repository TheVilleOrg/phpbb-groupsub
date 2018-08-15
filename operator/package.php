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
use stevotvr\groupsub\entity\package_interface as entity;

/**
 * Group Subscription package operator.
 */
class package extends operator implements package_interface
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

	public function get_packages($name = false)
	{
		$entities = array();

		$where = $name ? "WHERE pkg_ident = '" . $this->db->sql_escape($name) . "'" : '';
		$sql = 'SELECT *
				FROM ' . $this->package_table . '
				' . $where . '
				ORDER BY pkg_order ASC, pkg_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$entities[] = $this->container->get('stevotvr.groupsub.entity.package')->import($row);
		}
		$this->db->sql_freeresult($result);

		return $entities;
	}

	public function count_packages()
	{
		$sql = 'SELECT COUNT(pkg_id) AS pkg_count
				FROM ' . $this->package_table;
		$result = $this->db->sql_query($sql);
		$count = $this->db->sql_fetchfield('pkg_count');
		$this->db->sql_freeresult($result);

		return (int) $count;
	}

	public function add_package(entity $package)
	{
		$package->insert();
		$package_id = $package->get_id();
		return $package->load($package_id);
	}

	public function delete_package($package_id)
	{
		$sql = 'DELETE FROM ' . $this->sub_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->price_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->package_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}

	public function move_package($package_id, $offset)
	{
		$ids = array();

		$sql = 'SELECT pkg_id
				FROM ' . $this->package_table . '
				ORDER BY pkg_order ASC, pkg_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = $row['pkg_id'];
		}
		$this->db->sql_freeresult($result);

		$position = array_search($package_id, $ids);
		array_splice($ids, $position, 1);
		$position += $offset;
		array_splice($ids, $position, 0, $package_id);

		foreach ($ids as $pos => $id)
		{
			$sql = 'UPDATE ' . $this->package_table . '
					SET pkg_order = ' . $pos . '
					WHERE pkg_id = ' . (int) $id;
			$this->db->sql_query($sql);
		}
	}

	public function get_prices($package_id = false)
	{
		$entities = array();

		$where = $package_id ? 'WHERE pkg_id = ' . (int) $package_id : '';
		$sql = 'SELECT *
				FROM ' . $this->price_table . '
				' . $where . '
				ORDER BY price_order ASC, price_id ASC';
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$entities[(int) $row['pkg_id']][] = $this->container->get('stevotvr.groupsub.entity.price')->import($row);
		}
		$this->db->sql_freeresult();

		return $entities;
	}

	public function set_prices($package_id, array $prices)
	{
		$sql = 'DELETE FROM ' . $this->price_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);

		$i = 0;
		foreach ($prices as $entity)
		{
			$entity->set_package($package_id)->set_order($i++)->insert();
		}
	}

	public function get_groups($package_id)
	{
		$ids = array();

		$sql = 'SELECT group_id
				FROM ' . $this->group_table . '
				WHERE pkg_id = ' . (int) $package_id;
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
		$package_groups = array();

		$sql_ary = array(
			'SELECT'	=> 's.pkg_id, g.group_id, g.group_name',
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
			$package_groups[(int) $row['pkg_id']][] = array(
				'id'	=> (int) $row['group_id'],
				'name'	=> $this->group_helper->get_name($row['group_name']),
			);
		}
		$this->db->sql_freeresult($result);

		foreach ($package_groups as &$package_group)
		{
			$names = array_map('strtolower', array_column($package_group, 'name'));
			array_multisort($names, SORT_ASC, SORT_STRING, $package_group);
		}

		return $package_groups;
	}

	public function add_group($package_id, $group_id)
	{
		$data = array(
			'pkg_id'	=> (int) $package_id,
			'group_id'	=> (int) $group_id,
		);
		$sql = 'INSERT INTO ' . $this->group_table . '
				' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);
	}

	public function remove_group($package_id, $group_id)
	{
		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE pkg_id = ' . (int) $package_id . '
					AND group_id = ' . (int) $group_id;
		$this->db->sql_query($sql);
	}

	public function remove_groups($package_id)
	{
		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);
	}

	public function get_length($package_id, $price, $currency)
	{
		$sql = 'SELECT price_length
				FROM ' . $this->price_table . '
				WHERE pkg_id = ' . (int) $package_id . '
					AND price_price = ' . (int) $price . '
					AND price_currency = ' . $this->db->sql_escape($currency);
		$this->db->sql_query($sql);
		$length = $this->db->sql_fetchfield('price_length');
		$this->db->sql_freeresult();

		return $length === false ? false : (int) $length;
	}
}
