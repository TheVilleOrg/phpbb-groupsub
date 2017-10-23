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
 * Group Subscription product operator interface.
 */
interface product_interface
{
	/**
	 * Get all products.
	 *
	 * @param string $name The identifier of the product, false to get all products
	 *
	 * @return array An array of product entities
	 */
	public function get_products($name = false);

	/**
	 * Add a product.
	 *
	 * @param \stevotvr\groupsub\entity\product_interface $product
	 */
	public function add_product(\stevotvr\groupsub\entity\product_interface $product);

	/**
	 * Delete a product.
	 *
	 * @param int $product_id The product ID
	 *
	 * @return boolean The product was deleted
	 */
	public function delete_product($product_id);

	/**
	 * Move a product in the sorting order.
	 *
	 * @param int $product_id The product ID
	 * @param int $offset     The offset by which to move the product
	 */
	public function move_product($product_id, $offset);

	/**
	 * Get the groups assigned to a product.
	 *
	 * @param int $product_id The product ID
	 *
	 * @return array An array of group IDs
	 */
	public function get_groups($product_id);

	/**
	 * Get the group information for all products.
	 *
	 * @return array Array of associative arrays of group information
	 */
	public function get_all_groups();

	/**
	 * Add a group to a product.
	 *
	 * @param int $product_id The product ID
	 * @param int $group_id   The group ID
	 */
	public function add_group($product_id, $group_id);

	/**
	 * Remove a group from a product.
	 *
	 * @param int $product_id The product ID
	 * @param int $group_id   The group ID
	 */
	public function remove_group($product_id, $group_id);

	/**
	 * Remove all groups from a product.
	 *
	 * @param int $product_id The product ID
	 */
	public function remove_groups($product_id);
}
