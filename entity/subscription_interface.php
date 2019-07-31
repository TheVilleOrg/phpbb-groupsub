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
	 * @return int The package ID
	 */
	public function get_package();

	/**
	 * @param int $package_id The package ID
	 *
	 * @return subscription_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_package($package_id);

	/**
	 * @return int The user ID
	 */
	public function get_user();

	/**
	 * @param int $user_id The user ID
	 *
	 * @return subscription_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_user($user_id);

	/**
	 * @return int The creation time as a Unix timestamp
	 */
	public function get_start();

	/**
	 * @param int $start The creation time as a Unix timestamp
	 *
	 * @return subscription_interface This object for chaining
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function set_start($start);

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

	/**
	 * @return string The PayPal subscription ID
	 */
	public function get_paypal_id();

	/**
	 * @param string $paypal_id The PayPal subscription ID
	 *
	 * @return subscription_interface This object for chaining
	 */
	public function set_paypal_id($paypal_id);
}
