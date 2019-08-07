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
	'ACP_GROUPSUB_SETTINGS_GENERAL'			=> 'General options',
	'ACP_GROUPSUB_HEADER'					=> 'Page header',
	'ACP_GROUPSUB_HEADER_EXPLAIN'			=> 'Information to display at the top of all subscription pages.',
	'ACP_GROUPSUB_FOOTER'					=> 'Page footer',
	'ACP_GROUPSUB_FOOTER_EXPLAIN'			=> 'Information to display at the bottom of all subscription pages.',
	'ACP_GROUPSUB_SETTINGS_DEFAULTS'		=> 'Package defaults',
	'ACP_GROUPSUB_DEFAULT_CURRENCY'			=> 'Default currency',
	'ACP_GROUPSUB_DEFAULT_CURRENCY_EXPLAIN'	=> 'This is the default currency for all new packages, which can be overridden on a per-package basis.',
	'ACP_GROUPSUB_WARN_TIME'				=> 'Warning time',
	'ACP_GROUPSUB_WARN_TIME_EXPLAIN'		=> 'The number of days before the expiration of a subscription to notify the subscriber.',
	'ACP_GROUPSUB_GRACE'					=> 'Grace period',
	'ACP_GROUPSUB_GRACE_EXPLAIN'			=> 'The number of days after a subscription ends before removing the user from groups.',

	'ACP_GROUPSUB_MANAGE_PKGS'						=> 'Manage packages',
	'ACP_GROUPSUB_MANAGE_PKGS_TITLE'				=> 'Manage subscription packages',
	'ACP_GROUPSUB_MANAGE_PKGS_EXPLAIN'				=> 'Here you can manage the subscription packages that are available.',
	'ACP_GROUPSUB_NO_PKGS'							=> 'No subscription packages',
	'ACP_GROUPSUB_PKG_ADD'							=> 'Create subscription package',
	'ACP_GROUPSUB_PKG_ADD_SUCCESS'					=> 'Subscription package created successfully',
	'ACP_GROUPSUB_PKG_EDIT'							=> 'Edit subscription package',
	'ACP_GROUPSUB_PKG_EDIT_SUCCESS'					=> 'Subscription package details saved successfully',
	'ACP_GROUPSUB_PKG_DELETE_CONFIRM'				=> 'Are you sure you wish to delete this subscription package?',
	'ACP_GROUPSUB_PKG_DELETE_SUCCESS'				=> 'Package deleted successfully',
	'ACP_GROUPSUB_PKG_DETAILS'						=> 'Package details',
	'ACP_GROUPSUB_PKG_ENABLE'						=> 'Enable package',
	'ACP_GROUPSUB_PKG_ENABLE_EXPLAIN'				=> 'Make this package available to users.',
	'ACP_GROUPSUB_PKG_IDENT'						=> 'Package identifier',
	'ACP_GROUPSUB_PKG_IDENT_EXPLAIN'				=> 'A unique string to identify the package. The value must contain only a-z, 0-9, _, and begin with a letter.',
	'ACP_GROUPSUB_PKG_NAME'							=> 'Package name',
	'ACP_GROUPSUB_PKG_DESC'							=> 'Package description',
	'ACP_GROUPSUB_PKG_GROUPS_ADD_PERM'				=> 'Package add permanent groups',
	'ACP_GROUPSUB_PKG_GROUPS_ADD_PERM_EXPLAIN'		=> 'Select one or more groups to which to grant permanent access to subscribers.',
	'ACP_GROUPSUB_PKG_GROUPS_ADD_TEMP'				=> 'Package add subscription groups',
	'ACP_GROUPSUB_PKG_GROUPS_ADD_TEMP_EXPLAIN'		=> 'Select one or more groups to which to grant subscription access to subscribers.',
	'ACP_GROUPSUB_PKG_GROUPS_REMOVE_TEMP'			=> 'Package remove subscription groups',
	'ACP_GROUPSUB_PKG_GROUPS_REMOVE_TEMP_EXPLAIN'	=> 'Select one or more groups to which to cease subscription access to subscribers.',
	'ACP_GROUPSUB_PKG_GROUPS_REMOVE_PERM'			=> 'Package remove permanent groups',
	'ACP_GROUPSUB_PKG_GROUPS_REMOVE_PERM_EXPLAIN'	=> 'Select one or more groups to which to cease permanent access to subscribers.',
	'ACP_GROUPSUB_PKG_DEFAULT_GROUP'				=> 'Set default group',
	'ACP_GROUPSUB_PKG_DEFAULT_GROUP_EXPLAIN'		=> 'Optionally select a group to set as the default group for subscribers.',
	'ACP_GROUPSUB_PKG_TERM_ADD'						=> 'Add term',
	'ACP_GROUPSUB_PKG_TERMS'						=> 'Subscription terms',
	'ACP_GROUPSUB_PKG_PRICE'						=> 'Subscription price',
	'ACP_GROUPSUB_PKG_PRICE_EXPLAIN'				=> 'Enter the price for the subscription.',
	'ACP_GROUPSUB_PKG_LENGTH'						=> 'Subscription length',
	'ACP_GROUPSUB_PKG_LENGTH_EXPLAIN'				=> 'Enter the length of the subscription. Enter 0 for a never-ending subscription.',

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
	'ACP_GROUPSUB_SUB_PACKAGE'			=> 'Package',
	'ACP_GROUPSUB_SUB_SELECT_PACKAGE'	=> 'Select package',
	'ACP_GROUPSUB_SUB_EXPIRE'			=> 'Expires',
	'ACP_GROUPSUB_SUB_EXPIRE_EXPLAIN'	=> 'Enter the date at which this subscription should end. Leave this field blank for a never-ending subscription.',
	'ACP_GROUPSUB_SUB_START'			=> 'Started',
	'ACP_GROUPSUB_SUB_START_EXPLAIN'	=> 'Enter a starting date for this subscription.',

	'ACP_GROUPSUB_ERROR_CURRENCY'		=> 'You must select a valid currency.',
	'ACP_GROUPSUB_ERROR_NO_PKGS'		=> 'There are no packages for which to create a subscription.',
	'ACP_GROUPSUB_ERROR_DATE_IN_PAST'	=> 'The expiration date entered was in the past.',
	'ACP_GROUPSUB_ERROR_INVALID_DATE'	=> 'The date entered was in an invalid format.',
	'ACP_GROUPSUB_ERROR_MISSING_TERMS'	=> 'A package must have at least one term to be enabled.',
	'ACP_GROUPSUB_ERROR_SUB_CONFLICT'	=> 'This member already has an active subscription to this package.<br><br><a href="%s">Edit active subscription</a>',

	'ACP_GROUPSUB_TRANSACTIONS'	=> 'View transactions',
	'ACP_GROUPSUB_NO_TRANS'		=> 'No transactions',

	'ACP_GROUPSUB_PKG'			=> 'Package',
	'ACP_GROUPSUB_NAME'			=> 'Name',
	'ACP_GROUPSUB_TERMS'		=> 'Terms',
	'ACP_GROUPSUB_PRICE'		=> 'Price',
	'ACP_GROUPSUB_LENGTH'		=> 'Length',
	'ACP_GROUPSUB_USER'			=> 'Subscriber',
	'ACP_GROUPSUB_SUB'			=> 'Subscription',
	'ACP_GROUPSUB_START'		=> 'Started',
	'ACP_GROUPSUB_EXPIRES'		=> 'Expires',
	'ACP_GROUPSUB_STATUS'		=> 'Status',
	'ACP_GROUPSUB_TRANS_ID'		=> 'ID',
	'ACP_GROUPSUB_TRANS_TYPE'	=> 'Type',
	'ACP_GROUPSUB_AMOUNT'		=> 'Amount',
	'ACP_GROUPSUB_TIME'			=> 'Time',
	'ACP_GROUPSUB_LIVE'			=> 'Live',
	'ACP_GROUPSUB_SB'			=> 'Sandbox',
	'ACP_GROUPSUB_MORE'			=> '+%d more…',
	'ACP_GROUPSUB_VIEW'			=> 'View',

	'ACP_GROUPSUB_EXPIRES_UNLIMITED'	=> 'Unlimited',
	'ACP_GROUPSUB_EXPIRES_NEVER'		=> 'Never',
	'ACP_GROUPSUB_ACTIVE'				=> 'Active',
	'ACP_GROUPSUB_ENDED'				=> 'Ended',
	'ACP_GROUPSUB_ALL_PACKAGES'			=> 'All subscription packages',
	'ACP_GROUPSUB_SUBS_PER_PAGE'		=> 'Items per page',
	'ACP_GROUPSUB_DELETED'				=> 'deleted',
));
