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
	'GROUPSUB_NOTIFICATION_GROUP'				=> 'Group Subscription Benachrichtigungen',
	'GROUPSUB_NOTIFICATION_TYPE_WARN'			=> 'Deine Subscription läuft bald ab',
	'GROUPSUB_NOTIFICATION_TYPE_EXPIRED'		=> 'Deine Subscription ist abgelaufen',
	'GROUPSUB_NOTIFICATION_TYPE_STARTED'		=> 'Deine Subscription hat begonnen',
	'GROUPSUB_NOTIFICATION_TYPE_ADMIN_STARTED'	=> 'Eine Benutzer-subscription hat begonnen',

	'GROUPSUB_NOTIFICATION_WARN_TITLE'		=> 'Subscription läuft ab',
	'GROUPSUB_NOTIFICATION_WARN_REFERENCE'	=> 'Deine Subscription für <strong>%1$s</strong> läuft ab in %2$s.',

	'GROUPSUB_NOTIFICATION_EXPIRED_TITLE'		=> 'Subscription abgelaufen',
	'GROUPSUB_NOTIFICATION_EXPIRED_REFERENCE'	=> 'Deine Subscription für <strong>%s</strong> ist abgelaufen.',

	'GROUPSUB_NOTIFICATION_CANCELLED_TITLE'		=> 'Subscriptionabgebrochen',
	'GROUPSUB_NOTIFICATION_CANCELLED_REFERENCE'	=> 'Deine Subscription für <strong>%s</strong> wurde abgebrochen.',

	'GROUPSUB_NOTIFICATION_STARTED_TITLE'		=> 'Subscription gestartet',
	'GROUPSUB_NOTIFICATION_STARTED_REFERENCE'	=> 'Deine Subscription für <strong>%s</strong> hat begonnen.',

	'GROUPSUB_NOTIFICATION_ADMIN_STARTED_TITLE'		=> 'Benutzer-Subscription hat begonnen',
	'GROUPSUB_NOTIFICATION_ADMIN_STARTED_REFERENCE'	=> '%s hat Subscription für <strong>%s</strong> begonnen.',
));
