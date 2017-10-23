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
 * Group Subscription unit helper operator interface.
 */
interface unit_helper_interface
{
	/**
	 * Time units
	 */
	const WEEK = 7;
	const MONTH = 30;
	const YEAR = 365;

	/**
	 * Get a formatted time length from a number of days.
	 *
	 * @param int $days The length of time in days
	 *
	 * @return string The formatted time length
	 */
	public function get_formatted_timespan($days);

	/**
	 * Get the largest unit of time from a number of days.
	 *
	 * @param int $days The length of time in days
	 *
	 * @return array Associative array of length and unit
	 */
	public function get_timespan_parts($days);

	/**
	 * Get the number of days represented by a unit of time.
	 *
	 * @param int    $length The length of time
	 * @param string $unit   days, weeks, months, or years
	 *
	 * @return int The length of time in days
	 *
	 * @throws \stevotvr\groupsub\exception\unexpected_value
	 */
	public function get_days($length, $unit);
}
