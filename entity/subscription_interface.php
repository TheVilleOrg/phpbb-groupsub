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
 * Group Subscription subscription entity interface.
 */
interface subscription_interface extends entity_interface
{
	/**
	 * @return int The product ID
	 */
	public function get_product();

	/**
	 * @param int $product The product ID
	 *
	 * @return subscription_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_product($product);

	/**
	 * @return int The user ID
	 */
	public function get_user();

	/**
	 * @param int $user The user ID
	 *
	 * @return subscription_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_user($user);

	/**
	 * @return int The expiration time as a Unix timestamp
	 */
	public function get_expire();

	/**
	 * @param int $expire The expiration time as a Unix timestamp
	 *
	 * @return subscription_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_expire($expire);
}
