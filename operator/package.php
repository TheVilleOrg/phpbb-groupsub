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

use stevotvr\groupsub\entity\package_interface as entity;
use stevotvr\groupsub\exception\base;

/**
 * Group Subscription package operator.
 */
class package extends operator implements package_interface
{
	public function get_package_list()
	{
		$packages = array();

		$sql = 'SELECT pkg_id, pkg_name
				FROM ' . $this->package_table . '
				WHERE pkg_enabled = 1
				ORDER BY pkg_name ASC';
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$packages[(int) $row['pkg_id']] = $row['pkg_name'];
		}
		$this->db->sql_freeresult();

		return $packages;
	}

	public function get_packages($name = false, $enabled = true)
	{
		$packages = array();

		$where = 'pkg_deleted = 0';
		$where .= $enabled ? ' AND pkg_enabled = 1' : '';
		$where .= $name ? " AND pkg_ident = '" . $this->db->sql_escape($name) . "'" : '';
		$sql = 'SELECT *
				FROM ' . $this->package_table . '
				WHERE ' . $where . '
				ORDER BY pkg_order ASC, pkg_id ASC';
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$packages[(int) $row['pkg_id']] = array(
				'package'	=> $this->container->get('stevotvr.groupsub.entity.package')->import($row),
				'terms'		=> array(),
				'groups'	=> array(),
			);
		}
		$this->db->sql_freeresult();

		if (empty($packages))
		{
			return $packages;
		}

		$sql = 'SELECT *
				FROM ' . $this->term_table . '
				WHERE ' . $this->db->sql_in_set('pkg_id', array_keys($packages));
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$packages[(int) $row['pkg_id']]['terms'][] = $this->container->get('stevotvr.groupsub.entity.term')->import($row);
		}
		$this->db->sql_freeresult();

		return $packages;
	}

	public function count_packages()
	{
		$sql = 'SELECT COUNT(pkg_id) AS pkg_count
				FROM ' . $this->package_table . '
				WHERE pkg_enabled = 1';
		$this->db->sql_query($sql);
		$count = $this->db->sql_fetchfield('pkg_count');
		$this->db->sql_freeresult();

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
		$sql = 'DELETE FROM ' . $this->group_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->term_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);

		$sql = 'SELECT 1
				FROM ' . $this->sub_table . '
				WHERE pkg_id = ' . (int) $package_id . '
				LIMIT 1';
		$this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult();

		if ($row)
		{
			$data = array(
				'pkg_ident'					=> null,
				'pkg_desc'					=> '',
				'pkg_desc_bbcode_uid'		=> '',
				'pkg_desc_bbcode_bitfield'	=> '',
				'pkg_desc_bbcode_options'	=> 0,
				'pkg_order'					=> 0,
				'pkg_enabled'				=> 0,
				'pkg_deleted'				=> 1,
			);
			$sql = 'UPDATE ' . $this->package_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $data) . '
					WHERE pkg_id = ' . (int) $package_id;
			$this->db->sql_query($sql);
		}
		else
		{
			$sql = 'DELETE FROM ' . $this->package_table . '
					WHERE pkg_id = ' . (int) $package_id;
			$this->db->sql_query($sql);
		}

		return (bool) $this->db->sql_affectedrows();
	}

	public function move_package($package_id, $offset)
	{
		$ids = array();

		$sql = 'SELECT pkg_id
				FROM ' . $this->package_table . '
				WHERE pkg_deleted = 0
				ORDER BY pkg_order ASC, pkg_id ASC';
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$ids[] = $row['pkg_id'];
		}
		$this->db->sql_freeresult();

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

	public function get_terms($package_id = false)
	{
		$entities = array();

		$where = $package_id ? 'WHERE pkg_id = ' . (int) $package_id : '';
		$sql = 'SELECT *
				FROM ' . $this->term_table . '
				' . $where . '
				ORDER BY term_order ASC, term_id ASC';
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$entities[(int) $row['pkg_id']][] = $this->container->get('stevotvr.groupsub.entity.term')->import($row);
		}
		$this->db->sql_freeresult();

		return $entities;
	}

	public function set_terms($package_id, array $terms)
	{
		$sql = 'DELETE FROM ' . $this->term_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);

		$i = 0;
		foreach ($terms as $entity)
		{
			$entity->set_package($package_id)->set_order($i++)->insert();
		}
	}

	public function get_package_term($term_id)
	{
		$sql_ary = array(
			'SELECT'	=> '*',
			'FROM'		=> array(
				$this->term_table		=> 't',
				$this->package_table	=> 'p',
			),
			'WHERE'		=> 't.term_id = ' . (int) $term_id . '
							AND p.pkg_id = t.pkg_id',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow();
		$this->db->sql_freeresult();

		if (!$row)
		{
			return false;
		}

		try
		{
			return array(
				'package'	=> $this->container->get('stevotvr.groupsub.entity.package')->import($row),
				'term'		=> $this->container->get('stevotvr.groupsub.entity.term')->import($row),
			);
		}
		catch (base $e)
		{
			return false;
		}
	}

	public function get_groups($package_id, &$default = 0)
	{
		$ids = array();

		$sql = 'SELECT group_id, group_default
				FROM ' . $this->group_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$ids[] = (int) $row['group_id'];
			if ($row['group_default'])
			{
				$default = (int) $row['group_id'];
			}
		}
		$this->db->sql_freeresult();

		return $ids;
	}

	public function add_group($package_id, $group_id, $default)
	{
		$data = array(
			'pkg_id'		=> (int) $package_id,
			'group_id'		=> (int) $group_id,
			'group_default'	=> (bool) $default,
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
}
