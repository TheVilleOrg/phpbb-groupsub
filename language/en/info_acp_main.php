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
	'ACP_GROUPSUB_TITLE'	=> 'Group Subscription',

	'ACP_GROUPSUB_SETTINGS'					=> 'Settings',
	'ACP_GROUPSUB_SETTINGS_TITLE'			=> 'Group Subscription settings',
	'ACP_GROUPSUB_SETTINGS_SAVED'			=> 'Group Subscription options saved successfully',
	'ACP_GROUPSUB_SETTINGS_PAYPAL'			=> 'PayPal settings',
	'ACP_GROUPSUB_PP_SANDBOX'				=> 'Enable sandbox mode',
	'ACP_GROUPSUB_PP_SANDBOX_EXPLAIN'		=> 'Sandbox mode allows you to test PayPal payments without using real funds.',
	'ACP_GROUPSUB_PP_SB_BUSINESS'			=> 'Sandbox email address',
	'ACP_GROUPSUB_PP_SB_BUSINESS_EXPLAIN'	=> 'This is the email address for your PayPal Sandbox account.',
	'ACP_GROUPSUB_PP_BUSINESS'				=> 'PayPal email address',
	'ACP_GROUPSUB_PP_BUSINESS_EXPLAIN'		=> 'This is the email address for the PayPal account that will accept payments',
	'ACP_GROUPSUB_SETTINGS_DEFAULTS'		=> 'Product defaults',
	'ACP_GROUPSUB_DEFAULT_CURRENCY'			=> 'Default currency',
	'ACP_GROUPSUB_DEFAULT_CURRENCY_EXPLAIN'	=> 'This is the default currency for all new products, which can be overridden on a per-product basis.',
	'ACP_GROUPSUBB_DEFAULT_WARN_TIME'		=> 'Default warning time',
	'ACP_GROUPSUBB_DEFAULT_GRACE'			=> 'Default grace period',

	'ACP_GROUPSUB_MANAGE_PRODS'				=> 'Manage products',
	'ACP_GROUPSUB_MANAGE_PRODS_EXPLAIN'		=> 'Here you can manage the subscription options that are available.',
	'ACP_GROUPSUB_NO_PRODS'					=> 'No products',
	'ACP_GROUPSUB_PROD_ADD'					=> 'Create product',
	'ACP_GROUPSUB_PROD_ADD_SUCCESS'			=> 'Product created successfully',
	'ACP_GROUPSUB_PROD_EDIT'				=> 'Edit product',
	'ACP_GROUPSUB_PROD_EDIT_SUCCESS'		=> 'Product details saved successfully',
	'ACP_GROUPSUB_PROD_DELETE_CONFIRM'		=> 'Are you sure you wish to delete this product?',
	'ACP_GROUPSUB_PROD_DELETE_SUCCESS'		=> 'Product deleted successfully',
	'ACP_GROUPSUB_PROD_DETAILS'				=> 'Product details',
	'ACP_GROUPSUB_PROD_IDENT'				=> 'Product identifier',
	'ACP_GROUPSUB_PROD_IDENT_EXPLAIN'		=> 'A unique string to identify the product. The value must contain only a-z, 0-9, _, and begin with a letter.',
	'ACP_GROUPSUB_PROD_NAME'				=> 'Product name',
	'ACP_GROUPSUB_PROD_DESC'				=> 'Product description',
	'ACP_GROUPSUB_PROD_GROUPS'				=> 'Subscription groups',
	'ACP_GROUPSUB_PROD_GROUPS_EXPLAIN'		=> 'Select one or more groups to which to grant access to subscribers.',
	'ACP_GROUPSUB_PROD_SUB'					=> 'Subscription details',
	'ACP_GROUPSUB_PROD_PRICE'				=> 'Subscription price',
	'ACP_GROUPSUB_PROD_PRICE_EXPLAIN'		=> 'Enter the price for the subscription.',
	'ACP_GROUPSUB_PROD_LENGTH'				=> 'Subscription length',
	'ACP_GROUPSUB_PROD_LENGTH_EXPLAIN'		=> 'Enter the length of the subscription. Enter 0 for a never-ending subscription.',
	'ACP_GROUPSUB_PROD_WARN_TIME'			=> 'Warning time',
	'ACP_GROUPSUB_PROD_WARN_TIME_EXPLAIN'	=> 'The number of days before the expiration of a subscription to notify the subscriber.',
	'ACP_GROUPSUB_PROD_GRACE'				=> 'Grace period',
	'ACP_GROUPSUB_PROD_GRACE_EXPLAIN'		=> 'The number of days after a subscription ends before removing the user from groups.',

	'ACP_GROUPSUB_MANAGE_SUBS'			=> 'Manage subscriptions',
	'ACP_GROUPSUB_MANAGE_SUBS_EXPLAIN'	=> 'Here you can view, modify, and cancel subscriptions.',
	'ACP_GROUPSUB_NO_SUBS'				=> 'No subscriptions',
	'ACP_GROUPSUB_SUB_ADD'				=> 'Create subscription',
	'ACP_GROUPSUB_SUB_ADD_SUCCESS'		=> 'Subscription created successfully',
	'ACP_GROUPSUB_SUB_EDIT'				=> 'Edit subscription',
	'ACP_GROUPSUB_SUB_EDIT_SUCCESS'		=> 'Subscription details saved successfully',
	'ACP_GROUPSUB_SUB_DELETE_CONFIRM'	=> 'Are you sure you wish to cancel this subscription?',
	'ACP_GROUPSUB_SUB_DELETE_SUCCESS'	=> 'Subscription cancelled successfully',
	'ACP_GROUPSUB_SUB_DETAILS'			=> 'Subscription details',
	'ACP_GROUPSUB_SUB_USER'				=> 'Subscriber',
	'ACP_GROUPSUB_SUB_PRODUCT'			=> 'Product',
	'ACP_GROUPSUB_SUB_EXPIRE'			=> 'Expires',
	'ACP_GROUPSUB_SUB_EXPIRE_EXPLAIN'	=> 'Enter the date at which this subscription should end. Leave this field blank for a never-ending subscription',

	'ACP_GROUPSUB_ERROR_CURRENCY'		=> 'You must select a valid currency.',
	'ACP_GROUPSUB_ERROR_NO_PRODS'		=> 'There are no products for which to create a subscription.',
	'ACP_GROUPSUB_ERROR_INVALID_DATE'	=> 'The expiration date entered was in an invalid format.',
	'ACP_GROUPSUB_ERROR_INVALID_PRICE'	=> 'The price must be greater than 0.',

	'ACP_GROUPSUB_PROD'		=> 'Product',
	'ACP_GROUPSUB_NAME'		=> 'Name',
	'ACP_GROUPSUB_PRICE'	=> 'Price',
	'ACP_GROUPSUB_LENGTH'	=> 'Length',
	'ACP_GROUPSUB_USER'		=> 'Subscriber',
	'ACP_GROUPSUB_SUB'		=> 'Subscription',
	'ACP_GROUPSUB_EXPIRES'	=> 'Expires',

	'ACP_GROUPSUB_EXPIRES_UNLIMITED'	=> 'Unlimited',
	'ACP_GROUPSUB_EXPIRES_NEVER'		=> 'Never',
	'ACP_GROUPSUB_ALL_PRODUCTS'			=> 'All products',
	'ACP_GROUPSUB_SUBS_PER_PAGE'		=> 'Items per page',
));
