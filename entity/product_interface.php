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

/**
 * Group Subscription product entity interface.
 */
interface product_interface extends entity_interface
{
	/**
	 * @return string The unique identifier string for this product
	 */
	public function get_ident();

	/**
	 * @param string $ident The unique identifier string for this product
	 *
	 * @return product_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\unexpected_value
	 */
	public function set_ident($ident);

	/**
	 * @return string The name of this product
	 */
	public function get_name();

	/**
	 * @param string $name The name of this product
	 *
	 * @return product_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\unexpected_value
	 */
	public function set_name($name);

	/**
	 * @return string The description of this product for editing
	 */
	public function get_desc_for_edit();

	/**
	 * @return string The description of this product for display
	 */
	public function get_desc_for_display();

	/**
	 * @param string $desc The description of this product
	 *
	 * @return product_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\unexpected_value
	 */
	public function set_desc($desc);

	/**
	 * @return boolean BBCode is enabled on the description
	 */
	public function is_bbcode_enabled();

	/**
	 * @param boolean $enable Enable BBCode on the description.
	 *
	 * @return product_interface This object for chaining
	 */
	public function set_bbcode_enabled($enable);

	/**
	 * @return boolean URL parsing is enabled on the description
	 */
	public function is_magic_url_enabled();

	/**
	 * @param boolean $enable Enable URL parsing on the description.
	 *
	 * @return product_interface This object for chaining
	 */
	public function set_magic_url_enabled($enable);

	/**
	 * @return boolean Smilies are enabled on the description
	 */
	public function is_smilies_enabled();

	/**
	 * @param boolean $enable Enable smilies on the description.
	 *
	 * @return product_interface This object for chaining
	 */
	public function set_smilies_enabled($enable);

	/**
	 * @return int The price of this product
	 */
	public function get_price();

	/**
	 * @param int $price The price of this product
	 *
	 * @return product_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_price($price);

	/**
	 * @return string The currency code of the price of this product
	 */
	public function get_currency();

	/**
	 * @param string $currency The currency code of the price of this product
	 *
	 * @return product_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\unexpected_value
	 */
	public function set_currency($currency);

	/**
	 * @return int The length of this product in days
	 */
	public function get_length();

	/**
	 * @param int $length The length of this product in days
	 *
	 * @return product_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_length($length);

	/**
	 * @return int The time in days before the expiration of this product at which to notify
	 *             subscribers
	 */
	public function get_warn_time();

	/**
	 * @param int $warn_time The time in days before the expiration of this product at which to
	 *                       notify subscribers
	 *
	 * @return product_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_warn_time($warn_time);

	/**
	 * @return int The time in days after the expiration of this product at which to remove
	 *             subscribers from usergroups
	 */
	public function get_grace();

	/**
	 * @param int $grace The time in days after the expiration of this product at which to remove
	 *                   subscribers from usergroups
	 *
	 * @return product_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_grace($grace);

	/**
	 * @return int The order of this product
	 */
	public function get_order();

	/**
	 * @param int $order The order of this product
	 *
	 * @return product_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_order($order);
}
