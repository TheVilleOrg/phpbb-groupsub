<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\entity;

use phpbb\db\driver\driver_interface;
use stevotvr\groupsub\exception\missing_field;
use stevotvr\groupsub\exception\out_of_bounds;

/**
 * Group Subscription entity base class.
 */
abstract class entity implements entity_interface
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * The data for this entity.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * The map of table columns to data types.
	 *
	 * @var array
	 */
	protected $columns = array();

	/**
	 * The name of the column representing the unique row identifier.
	 *
	 * @var string
	 */
	protected $id_column = 'id';

	/**
	 * The name of the database table.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param string                            $table_name The name of the database table
	 */
	public function __construct(driver_interface $db, $table_name)
	{
		$this->db = $db;
		$this->table_name = $table_name;
	}

	public function load($id)
	{
		$sql = 'SELECT *
				FROM ' . $this->table_name . '
				WHERE ' . $this->id_column . ' = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($this->data === false)
		{
			throw new out_of_bounds($this->id_column);
		}

		return $this;
	}

	public function import(array $data)
	{
		$this->data = array();

		foreach ($this->columns as $column => $type)
		{
			if (!isset($data[$column]))
			{
				throw new missing_field($column);
			}

			if (method_exists($this, $type))
			{
				$this->$type($data[$column]);
				continue;
			}

			if ($type === 'integer' && $data[$column] < 0)
			{
				throw new out_of_bounds($column);
			}

			$value = $data[$column];
			settype($value, $type);
			$this->data[$column] = $value;
		}

		return $this;
	}

	public function insert()
	{
		if (!empty($this->data[$this->id_column]))
		{
			throw new out_of_bounds($this->id_column);
		}

		$sql = 'INSERT INTO ' . $this->table_name . '
				' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

		$this->data[$this->id_column] = (int) $this->db->sql_nextid();

		return $this;
	}

	public function save()
	{
		if (empty($this->data[$this->id_column]))
		{
			throw new out_of_bounds($this->id_column);
		}

		$data = array_diff_key($this->data, array($this->id_column => null));
		$sql = 'UPDATE ' . $this->table_name . '
				SET ' . $this->db->sql_build_array('UPDATE', $data) . '
				WHERE ' . $this->id_column . ' = ' . (int) $this->get_id();
		$this->db->sql_query($sql);

		return $this;
	}

	public function get_id()
	{
		return isset($this->data[$this->id_column]) ? (int) $this->data[$this->id_column] : 0;
	}
}
