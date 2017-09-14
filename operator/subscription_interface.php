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
 * Group Subscription subscription operator interface.
 */
interface subscription_interface
{
	/**
	 * Get subscriptions.
	 *
	 * @param int $product_id The product ID, 0 to get all subscriptions
	 *
	 * @return array Array associative arrays of subscription data
	 *                     product	string
	 *                     username	string
	 *                     entity	\stevotvr\groupsub\entity\subscription_interface
	 */
	public function get_subscriptions($product_id = 0);

	/**
	 * Get a subscription.
	 *
	 * @param int $sub_id The subscription ID
	 *
	 * @return array Associative array of subscription data
	 *                     product	string
	 *                     username	string
	 *                     entity	\stevotvr\groupsub\entity\subscription_interface
	 *
	 * @throws \stevotvr\groupsub\exception\out_of_bounds
	 */
	public function get_subscription($sub_id);

	/**
	 * Add a subscription.
	 *
	 * @param \stevotvr\groupsub\entity\subscription_interface $subscription
	 */
	public function add_subscription(\stevotvr\groupsub\entity\subscription_interface $subscription);

	/**
	 * Delete a subscription.
	 *
	 * @param int $sub_id The subscription ID
	 *
	 * @return boolean The subscription was deleted
	 */
	public function delete_subscription($sub_id);

	/**
	 * Get all subscriptions for a user.
	 *
	 * @param int $user_id The user ID
	 *
	 * @return array An array of subscription entities
	 */
	public function get_user_subscriptions($user_id);

	/**
	 * Get all subscribed users of a group.
	 *
	 * @param int $group_id The group ID
	 *
	 * @return array An array of user IDs
	 */
	public function get_subscribed_users($group_id);
}
