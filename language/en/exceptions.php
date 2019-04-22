<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Steve Guidetti, https://github.com/stevotvr
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'EXCEPTION_MISSING_FIELD'		=> 'The required field “%s” is missing.',
	'EXCEPTION_OUT_OF_BOUNDS'		=> 'The field “%s” received a value out of its range.',
	'EXCEPTION_TOO_LONG'			=> 'The field “%s” received a value longer than its maximum length.',
	'EXCEPTION_NOT_UNIQUE'			=> 'The field “%s” received a value that is not unique.',
	'EXCEPTION_INVALID_CURRENCY'	=> 'The field “%s” received an invalid currency code.',
	'EXCEPTION_INVALID_IDENT'		=> 'The field “%s” must contain only a-z, 0-9, _, and begin with a letter.',

	'EXCEPTION_FIELD_PKG_IDENT'		=> 'Package identifier',
	'EXCEPTION_FIELD_PKG_NAME'		=> 'Package name',
	'EXCEPTION_FIELD_TERM_PRICE'	=> 'Subscription price',
	'EXCEPTION_FIELD_TERM_CURRENCY'	=> 'Subscription price',
	'EXCEPTION_FIELD_TERM_LENGTH'	=> 'Subscription length',
));
