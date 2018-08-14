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
	 * Count the total number of products.
	 *
	 * @return int The total number of products
	 */
	public function count_products();

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
	 * Get a list of the price options associated with a product.
	 *
	 * @param int $product_id The product ID or false to get all prices
	 *
	 * @return array Array of arrays of price entities keyed by product ID
	 */
	public function get_prices($product_id = false);

	/**
	 * Set the price options for a product.
	 *
	 * @param int   $product_id The product ID
	 * @param array $prices     Array of price entities
	 */
	public function set_prices($product_id, array $prices);

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

	/**
	 * Get the length of a subscription based on payment amount and currency.
	 *
	 * @param int   $product_id The product ID
	 * @param array $price      The price in the currency subunit
	 * @param array $currency   The currency code of the price
	 *
	 * @return int The length of the subscription in days
	 */
	public function get_length($product_id, $price, $currency);
}
