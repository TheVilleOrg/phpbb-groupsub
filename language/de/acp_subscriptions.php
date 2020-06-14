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
	'ACP_GROUPSUB_MANAGE_SUBS_EXPLAIN_READONLY'	=> 'Hier kannst du dir deine Subscriptions anzeigen lassen.',
	'ACP_GROUPSUB_MANAGE_SUBS_EXPLAIN'			=> 'Hier kannst du deine Subscriptions sehen, bearbeiten oder beenden.',
	'ACP_GROUPSUB_NO_SUBS'						=> 'Keine Subscriptions',
	'ACP_GROUPSUB_SUB_ADD'						=> 'Erstelle Subscription',
	'ACP_GROUPSUB_SUB_ADD_SUCCESS'				=> 'Subscription erfolgreich erstellt',
	'ACP_GROUPSUB_SUB_EDIT'						=> 'Bearbeite Subscription',
	'ACP_GROUPSUB_SUB_EDIT_SUCCESS'				=> 'Subscription Details erfolgreich gespeichert',
	'ACP_GROUPSUB_SUB_DELETE_CONFIRM'			=> 'Bist du sicher, dass du die Subscription beenden möchtest?',
	'ACP_GROUPSUB_SUB_DELETE_SUCCESS'			=> 'Subscription erfolgreich beendet',
	'ACP_GROUPSUB_SUB_DETAILS'					=> 'Subscription Details',
	'ACP_GROUPSUB_SUB_USER'						=> 'Subscriber',
	'ACP_GROUPSUB_SUB_PACKAGE'					=> 'Paket',
	'ACP_GROUPSUB_SUB_SELECT_PACKAGE'			=> 'Wähle Paket',
	'ACP_GROUPSUB_SUB_EXPIRE'					=> 'Läuft ab',
	'ACP_GROUPSUB_SUB_EXPIRE_EXPLAIN'			=> 'Trage das Datum ein, an dem die Subscription enden soll. Lass das Feld leer für eine unbegrenzte Subscription.',
	'ACP_GROUPSUB_SUB_START'					=> 'Gestartet',
	'ACP_GROUPSUB_SUB_START_EXPLAIN'			=> 'Gib ein Startdatum für diese Subscription ein.',

	'ACP_GROUPSUB_START'	=> 'Gestartet',
	'ACP_GROUPSUB_EXPIRES'	=> 'Läuft ab',
	'ACP_GROUPSUB_STATUS'	=> 'Status',

	'ACP_GROUPSUB_EXPIRES_NEVER'	=> 'Niemals',
	'ACP_GROUPSUB_ACTIVE'			=> 'Aktiv',
	'ACP_GROUPSUB_ENDED'			=> 'Beendet',
	'ACP_GROUPSUB_ALL_PACKAGES'		=> 'Alle Subscription Pakete',
	'ACP_GROUPSUB_SUBS_PER_PAGE'	=> 'Einträge pro Seite',
	'ACP_GROUPSUB_DELETED'			=> 'gelöscht',

	'ACP_GROUPSUB_ERROR_NO_PKGS'		=> 'Es gibt keine Pakete für das Erzeugen einer Subscription.',
	'ACP_GROUPSUB_ERROR_DATE_IN_PAST'	=> 'Das eingebenede Ablaufdatum liegt ein der Vergangenheit.',
	'ACP_GROUPSUB_ERROR_INVALID_DATE'	=> 'Das eingegebene Datum hat einungültiges Format.',
	'ACP_GROUPSUB_ERROR_SUB_CONFLICT'	=> 'Das Mitglied hat bereits eine altive Subscription für dieses Paket.<br><br><a href="%s">Bearbeite aktive Subscription</a>',
));
