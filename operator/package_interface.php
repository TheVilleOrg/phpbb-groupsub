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
	 * Get all packages.
	 *
	 * @param string $name The identifier of the package, false to get all packages
	 *
	 * @return array An array of package entities
	 */
	public function get_packages($name = false);

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
	 * Get a list of the price options associated with a package.
	 *
	 * @param int $package_id The package ID or false to get all prices
	 *
	 * @return array Array of arrays of price entities keyed by package ID
	 */
	public function get_prices($package_id = false);

	/**
	 * Set the price options for a package.
	 *
	 * @param int   $package_id The package ID
	 * @param array $prices     Array of price entities
	 */
	public function set_prices($package_id, array $prices);

	/**
	 * Get the groups assigned to a package.
	 *
	 * @param int $package_id The package ID
	 *
	 * @return array An array of group IDs
	 */
	public function get_groups($package_id);

	/**
	 * Get the group information for all packages.
	 *
	 * @return array Array of associative arrays of group information
	 */
	public function get_all_groups();

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

	/**
	 * Get the length of a subscription based on payment amount and currency.
	 *
	 * @param int   $package_id The package ID
	 * @param array $price      The price in the currency subunit
	 * @param array $currency   The currency code of the price
	 *
	 * @return int The length of the subscription in days
	 */
	public function get_length($package_id, $price, $currency);
}
