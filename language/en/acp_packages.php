<?php
/**
 *
 * Group Subscription. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Steve Guidetti, https://github.com/stevotvr
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
	'ACP_GROUPSUB_MANAGE_PKGS_TITLE'			=> 'Manage subscription packages',
	'ACP_GROUPSUB_MANAGE_PKGS_EXPLAIN'			=> 'Here you can manage the subscription packages that are available.',
	'ACP_GROUPSUB_NO_PKGS'						=> 'No subscription packages',
	'ACP_GROUPSUB_PKG_ADD'						=> 'Create subscription package',
	'ACP_GROUPSUB_PKG_ADD_SUCCESS'				=> 'Subscription package created successfully',
	'ACP_GROUPSUB_PKG_EDIT'						=> 'Edit subscription package',
	'ACP_GROUPSUB_PKG_EDIT_SUCCESS'				=> 'Subscription package details saved successfully',
	'ACP_GROUPSUB_PKG_DELETE_CONFIRM'			=> 'Are you sure you wish to delete this subscription package?',
	'ACP_GROUPSUB_PKG_DELETE_SUCCESS'			=> 'Package deleted successfully',
	'ACP_GROUPSUB_PKG_DETAILS'					=> 'Package details',
	'ACP_GROUPSUB_PKG_ENABLE'					=> 'Enable package',
	'ACP_GROUPSUB_PKG_ENABLE_EXPLAIN'			=> 'Make this package available to users.',
	'ACP_GROUPSUB_PKG_IDENT'					=> 'Package identifier',
	'ACP_GROUPSUB_PKG_IDENT_EXPLAIN'			=> 'A unique string to identify the package. The value must contain only a-z, 0-9, _, and begin with a letter.',
	'ACP_GROUPSUB_PKG_NAME'						=> 'Package name',
	'ACP_GROUPSUB_PKG_DESC'						=> 'Package description',
	'ACP_GROUPSUB_PKG_GROUPS'					=> 'Package groups',
	'ACP_GROUPSUB_PKG_GROUPS_EXPLAIN'			=> 'Select one or more groups to which to grant access to subscribers.',
	'ACP_GROUPSUB_PKG_DEFAULT_GROUP'			=> 'Set default group',
	'ACP_GROUPSUB_PKG_DEFAULT_GROUP_EXPLAIN'	=> 'Optionally select a group to set as the default group for subscribers.',
	'ACP_GROUPSUB_PKG_TERM_ADD'					=> 'Add term',
	'ACP_GROUPSUB_PKG_TERMS'					=> 'Subscription terms',
	'ACP_GROUPSUB_PKG_PRICE'					=> 'Subscription price',
	'ACP_GROUPSUB_PKG_PRICE_EXPLAIN'			=> 'Enter the price for the subscription.',
	'ACP_GROUPSUB_PKG_LENGTH'					=> 'Subscription length',
	'ACP_GROUPSUB_PKG_LENGTH_EXPLAIN'			=> 'Enter the length of the subscription. Enter 0 for a never-ending subscription.',

	'ACP_GROUPSUB_NAME'		=> 'Name',
	'ACP_GROUPSUB_TERMS'	=> 'Terms',
	'ACP_GROUPSUB_PRICE'	=> 'Price',
	'ACP_GROUPSUB_LENGTH'	=> 'Length',
	'ACP_GROUPSUB_MORE'		=> '+%d more…',

	'ACP_GROUPSUB_EXPIRES_UNLIMITED'	=> 'Unlimited',

	'ACP_GROUPSUB_ERROR_MISSING_TERMS'	=> 'A package must have at least one term to be enabled.',
));
