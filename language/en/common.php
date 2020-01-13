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
	'GROUPSUB_PACKAGE_LIST'		=> 'Subscriptions',
	'GROUPSUB_NO_PACKAGES'		=> 'There are no subscriptions available.',
	'GROUPSUB_NO_DESC'			=> 'No description available.',
	'GROUPSUB_SUBSCRIPTION'		=> 'Subscription',
	'GROUPSUB_PRICE'			=> 'Price',
	'GROUPSUB_LENGTH'			=> 'Length',
	'GROUPSUB_LENGTH_UNLIMITED'	=> 'Unlimited',
	'GROUPSUB_SUBSCRIBE'		=> 'Subscribe',
	'GROUPSUB_RENEW'			=> 'Renew subscription',
	'GROUPSUB_CHOOSE_TERM'		=> 'Subscribe to %s',
	'GROUPSUB_SUBSCRIBED'		=> 'You are subscribed forever',
	'GROUPSUB_SUBSCRIBED_UNTIL'	=> 'You are subscribed until %s',
	'GROUPSUB_CONFIRM'			=> 'Confirm subscription to %s',

	'GROUPSUB_RETURN_TITLE'		=> 'Thank You',
	'GROUPSUB_RETURN'			=> 'Subscribed',
	'GROUPSUB_RETURN_UNLIMITED'	=> '<strong>unlimited</strong> time',
	'GROUPSUB_RETURN_MESSAGE'	=> 'You have subscribed to <strong>%1$s</strong> for %2$s. Please allow a few minutes for your payment to be processed and your subscription to be activated.',

	'GROUPSUB_PP_LOCALE'	=> 'en_US',
	'GROUPSUB_PP_BUY_NOW'	=> 'Buy Now',

	'GROUPSUB_DECIMAL_SEPARATOR'	=> '.',
	'GROUPSUB_THOUSANDS_SEPARATOR'	=> ',',

	'GROUPSUB_DAYS'		=> array(
		1	=> 'day',
		2	=> 'days',
	),
	'GROUPSUB_WEEKS'	=> array(
		1	=> 'week',
		2	=> 'weeks',
	),
	'GROUPSUB_MONTHS'	=> array(
		1	=> 'month',
		2	=> 'months',
	),
	'GROUPSUB_YEARS'	=> array(
		1	=> 'year',
		2	=> 'years',
	),
));
