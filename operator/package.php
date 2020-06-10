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
	/**
	 * @inheritDoc
	 */
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

	/**
	 * @inheritDoc
	 */
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

	/**
	 * @inheritDoc
	 */
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

	/**
	 * @inheritDoc
	 */
	public function add_package(entity $package)
	{
		$package->insert();
		$package_id = $package->get_id();
		return $package->load($package_id);
	}

	/**
	 * @inheritDoc
	 */
	public function delete_package($package_id)
	{
		$sql = 'DELETE FROM ' . $this->action_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . $this->term_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);

		$sql = 'SELECT 1
				FROM ' . $this->sub_table . '
				WHERE pkg_id = ' . (int) $package_id;
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

	/**
	 * @inheritDoc
	 */
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

	/**
	 * @inheritDoc
	 */
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

	/**
	 * @inheritDoc
	 */
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

	/**
	 * @inheritDoc
	 */
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

	/**
	 * @inheritDoc
	 */
	public function get_start_actions($package_id)
	{
		return $this->get_actions($package_id, 0);
	}

	/**
	 * @inheritDoc
	 */
	public function add_start_action($package_id, $action, $param)
	{
		$this->add_action($package_id, 0, $action, $param);
	}

	/**
	 * @inheritDoc
	 */
	public function get_end_actions($package_id)
	{
		return $this->get_actions($package_id, 1);
	}

	/**
	 * @inheritDoc
	 */
	public function add_end_action($package_id, $action, $param)
	{
		$this->add_action($package_id, 1, $action, $param);
	}

	/**
	 * @inheritDoc
	 */
	public function remove_actions($package_id)
	{
		$sql = 'DELETE FROM ' . $this->action_table . '
				WHERE pkg_id = ' . (int) $package_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Get the subscription actions for a package.
	 *
	 * @param int $package_id The package ID
	 * @param int $event      The action event
	 *
	 * @return array An array of subscription actions for the specified event
	 */
	protected function get_actions($package_id, $event)
	{
		$actions = array();

		$sql = 'SELECT act_name, act_param
				FROM ' . $this->action_table . '
				WHERE pkg_id = ' . (int) $package_id . '
					AND act_event = ' . (int) $event;
		$this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow())
		{
			$actions[] = array(
				'name'	=> $row['act_name'],
				'param'	=> $row['act_param'],
			);
		}
		$this->db->sql_freeresult();

		return $actions;
	}

	/**
	 * Add a subscription action to a package.
	 *
	 * @param int    $package_id The package ID
	 * @param int    $event      The action event
	 * @param string $action     The name of the action
	 * @param string $param      The parameter for the action
	 */
	protected function add_action($package_id, $event, $action, $param)
	{
		$data = array(
			'pkg_id'	=> (int) $package_id,
			'act_event'	=> (int) $event,
			'act_name'	=> $action,
			'act_param'	=> $param,
		);
		$sql = 'INSERT INTO ' . $this->action_table . '
				' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);
	}
}
