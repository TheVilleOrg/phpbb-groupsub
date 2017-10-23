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
	/* Notification statuses */
	const NOTIFY_NONE		= 0;
	const NOTIFY_WARN		= 1;
	const NOTIFY_EXPIRED	= 2;

	/**
	 * @param int $start The offset for querying subscriptions
	 *
	 * @return \stevotvr\groupsub\operator\subscription_interface This object for chaining
	 */
	public function set_start($start);

	/**
	 * @param int $limit The limit for querying subscriptions
	 *
	 * @return \stevotvr\groupsub\operator\subscription_interface This object for chaining
	 */
	public function set_limit($limit);

	/**
	 * @param int $user_id The user ID for which to query subscriptions
	 *
	 * @return \stevotvr\groupsub\operator\subscription_interface This object for chaining
	 */
	public function set_user($user_id);

	/**
	 * @param int $prod_id The product ID for which to query subscriptions
	 *
	 * @return \stevotvr\groupsub\operator\subscription_interface This object for chaining
	 */
	public function set_product($prod_id);

	/**
	 * Set the sorting options for querying subscriptions.
	 *
	 * @param string  $field The name of the field by which to sort
	 * @param boolean $desc  Sort in descending order
	 *
	 * @return \stevotvr\groupsub\operator\subscription_interface This object for chaining
	 */
	public function set_sort($field, $desc = false);

	/**
	 * Get subscriptions.
	 *
	 * @return array Array associative arrays of subscription data
	 *                     product	string
	 *                     username	string
	 *                     entity	\stevotvr\groupsub\entity\subscription_interface
	 */
	public function get_subscriptions();

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
	 * Count the total number of subscription.
	 *
	 * @return int The total number of subscription
	 */
	public function count_subscriptions();

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
	 * Get all subscribed users of a group.
	 *
	 * @param int $group_id The group ID
	 *
	 * @return array An array of user IDs
	 */
	public function get_subscribed_users($group_id);

	/**
	 * Find all expiring subscriptions and remove the users from the associated groups.
	 */
	public function process_expiring();

	/**
	 * Notify subscribers of expiring and expired subscriptions.
	 */
	public function notify_subscribers();
}
