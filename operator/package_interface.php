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

/**
 * Group Subscription package operator interface.
 */
interface package_interface
{
	/**
	 * Get a list of package names.
	 *
	 * @return array Array of package names keyed by package IDs
	 */
	public function get_package_list();

	/**
	 * Get packages.
	 *
	 * @param string  $name    The identifier of the package, false to get all packages
	 * @param boolean $enabled Only show enabled packages
	 *
	 * @return array Associative array of package data
	 *                           package \stevotvr\groupsub\entity\package_interface
	 *                           terms   array
	 *                           	\stevotvr\groupsub\entity\term_interface
	 *                           groups  array
	 *                           	id   int
	 *                           	name string
	 */
	public function get_packages($name = false, $enabled = true);

	/**
	 * Count the total number of packages.
	 *
	 * @return int The total number of packages
	 */
	public function count_packages();

	/**
	 * Add a package.
	 *
	 * @param \stevotvr\groupsub\entity\package_interface $package
	 */
	public function add_package(\stevotvr\groupsub\entity\package_interface $package);

	/**
	 * Delete a package.
	 *
	 * @param int $package_id The package ID
	 *
	 * @return boolean The package was deleted
	 */
	public function delete_package($package_id);

	/**
	 * Move a package in the sorting order.
	 *
	 * @param int $package_id The package ID
	 * @param int $offset     The offset by which to move the package
	 */
	public function move_package($package_id, $offset);

	/**
	 * Get a list of the terms associated with a package.
	 *
	 * @param int $package_id The package ID or false to get all terms
	 *
	 * @return array Array of arrays of term entities keyed by package ID
	 */
	public function get_terms($package_id = false);

	/**
	 * Set the terms for a package.
	 *
	 * @param int   $package_id The package ID
	 * @param array $terms      Array of term entities
	 */
	public function set_terms($package_id, array $terms);

	/**
	 * Get package and term from a term ID.
	 *
	 * @param int $term_id Term ID
	 *
	 * @return array Associative array of
	 *                           package \stevotvr\groupsub\entity\package_interface
	 *                           term    \stevotvr\groupsub\entity\term_interface
	 */
	public function get_package_term($term_id);

	/**
	 * Get the groups assigned to a package.
	 *
	 * @param int $package_id The package ID
	 *
	 * @return array An array of group IDs
	 */
	public function get_groups($package_id);

	/**
	 * Add a group to a package.
	 *
	 * @param int $package_id The package ID
	 * @param int $group_id   The group ID
	 */
	public function add_group($package_id, $group_id);

	/**
	 * Remove a group from a package.
	 *
	 * @param int $package_id The package ID
	 * @param int $group_id   The group ID
	 */
	public function remove_group($package_id, $group_id);

	/**
	 * Remove all groups from a package.
	 *
	 * @param int $package_id The package ID
	 */
	public function remove_groups($package_id);
}
