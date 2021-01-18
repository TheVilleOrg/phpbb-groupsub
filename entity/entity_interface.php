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

use stevotvr\groupsub\exception\missing_field;
use stevotvr\groupsub\exception\out_of_bounds;

/**
 * Group Subscription entity interface.
 */
interface entity_interface
{
	/**
	 * Load an entity from the database.
	 *
	 * @param int $id The database ID of the entity
	 *
	 * @return entity_interface This object for chaining
	 *
	 * @throws out_of_bounds
	 */
	public function load($id);

	/**
	 * Import data from an external source.
	 *
	 * @param array $data The data to import
	 *
	 * @return entity_interface This object for chaining
	 *
	 * @throws missing_field
	 * @throws out_of_bounds
	 */
	public function import(array $data);

	/**
	 * Insert a new entity into the database.
	 *
	 * @return entity_interface This object for chaining
	 *
	 * @throws out_of_bounds
	 */
	public function insert();

	/**
	 * Save the current settings to the database.
	 *
	 * @return entity_interface This object for chaining
	 *
	 * @throws out_of_bounds
	 */
	public function save();

	/**
	 * @return int The database ID of the entity
	 */
	public function get_id();
}
