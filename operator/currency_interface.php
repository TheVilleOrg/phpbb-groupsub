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

use stevotvr\groupsub\exception\unexpected_value;

/**
 * Group Subscription currency operator interface.
 */
interface currency_interface
{
	/**
	 * @return array The currency data
	 */
	public function get_currencies();

	/**
	 * Format the value portion of a price.
	 *
	 * @param string  $currency_code  The currency code
	 * @param int     $value          The value in the subunit of the currency
	 * @param boolean $with_separator Include the thousands separator
	 * @param boolean $localized      Localize the thousands and decimal separators
	 *
	 * @return string The formatted value
	 *
	 * @throws unexpected_value
	 */
	public function format_value($currency_code, $value, $with_separator = false, $localized = true);

	/**
	 * Format a price.
	 *
	 * @param string $currency_code The currency code
	 * @param int    $value         The value in the subunit of the currency
	 *
	 * @return string The formatted price
	 *
	 * @throws unexpected_value
	 */
	public function format_price($currency_code, $value);

	/**
	 * Parse a formatted price value into the subunit of the currency.
	 *
	 * @param string $currency_code The currency code
	 * @param string $value         The formatted value
	 *
	 * @return int The value in the subunit of the currency
	 *
	 * @throws unexpected_value
	 */
	public function parse_value($currency_code, $value);

	/**
	 * Check whether the given currency code is valid.
	 *
	 * @param string $currency_code The currency code
	 *
	 * @return boolean The currency code is valid
	 */
	public function is_valid($currency_code);
}
