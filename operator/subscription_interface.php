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

use stevotvr\groupsub\entity\subscription_interface as entity;
use stevotvr\groupsub\entity\term_interface as term_entity;
use stevotvr\groupsub\exception\out_of_bounds;

/**
 * Group Subscription subscription operator interface.
 */
interface subscription_interface
{
	/**
	 * @param int $start The offset for querying subscriptions
	 *
	 * @return subscription_interface This object for chaining
	 */
	public function set_start($start);

	/**
	 * @param int $limit The limit for querying subscriptions
	 *
	 * @return subscription_interface This object for chaining
	 */
	public function set_limit($limit);

	/**
	 * @param int $user_id The user ID for which to query subscriptions
	 *
	 * @return subscription_interface This object for chaining
	 */
	public function set_user($user_id);

	/**
	 * @param int $package_id The package ID for which to query subscriptions
	 *
	 * @return subscription_interface This object for chaining
	 */
	public function set_package($package_id);

	/**
	 * @param boolean $active Active status for which to query subscriptions
	 *
	 * @return subscription_interface This object for chaining
	 */
	public function set_active($active);

	/**
	 * Set the sorting options for querying subscriptions.
	 *
	 * @param string  $field The name of the field by which to sort
	 * @param boolean $desc  Sort in descending order
	 *
	 * @return subscription_interface This object for chaining
	 */
	public function set_sort($field, $desc = false);

	/**
	 * Get subscriptions.
	 *
	 * @return array Array associative arrays of subscription data
	 *                     package
	 *                     	name    string
	 *                     	deleted boolean
	 *                     username	string
	 *                     entity	entity
	 */
	public function get_subscriptions();

	/**
	 * Get a subscription.
	 *
	 * @param int $sub_id The subscription ID
	 *
	 * @return array Associative array of subscription data
	 *                     package
	 *                     	name    string
	 *                     	deleted boolean
	 *                     username	string
	 *                     entity	entity
	 *
	 * @throws out_of_bounds
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
	 * @param entity $subscription
	 *
	 * @return int The subscription ID
	 */
	public function add_subscription(entity $subscription);

	/**
	 * Create or extend a subscription for a user.
	 *
	 * @param term_entity $term
	 * @param int         $user_id The user ID
	 *
	 * @return int The subscription ID
	 */
	public function create_subscription(term_entity $term, $user_id);

	/**
	 * Start a subscription.
	 *
	 * @param int $sub_id The subscription ID
	 */
	public function start_subscription($sub_id);

	/**
	 * Restart a subscription.
	 *
	 * @param int $sub_id The subscription ID
	 */
	public function restart_subscription($sub_id);

	/**
	 * Delete a subscription.
	 *
	 * @param int $sub_id The subscription ID
	 */
	public function delete_subscription($sub_id);

	/**
	 * Find all expiring subscriptions and remove the users from the associated groups.
	 */
	public function process_expiring();

	/**
	 * Notify subscribers of expiring and expired subscriptions.
	 */
	public function notify_subscribers();

	/**
	 * Find an active subscription that conflicts with the given subscription if one exists.
	 *
	 * @return int|boolean The conflicting subscription ID or false if none exists
	 *
	 * @param entity $subscription
	 */
	public function get_conflict(entity $subscription);

	/**
	 * Get a list of active subscriptions for a user.
	 *
	 * @param int $user_id The user ID
	 *
	 * @return array Array of subscription entities keyed by package ID
	 */
	public function get_user_subscriptions($user_id);
}
