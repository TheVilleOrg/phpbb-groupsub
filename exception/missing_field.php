<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace stevotvr\groupsub\exception;

/**
 * Group Subscription exception for a missing required field.
 */
class missing_field extends base
{
	/**
	 * @param string $field The name of the required field
	 */
	public function __construct($field)
	{
		$message = sprintf('The required field "%s" is missing.', $field);
		$lang_array = array('EXCEPTION_MISSING_FIELD', 'EXCEPTION_FIELD_' . strtoupper($field));
		parent::__construct($message, $lang_array);
	}
}
