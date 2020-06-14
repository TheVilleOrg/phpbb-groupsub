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
	'ACP_GROUPSUB_MANAGE_PKGS_TITLE'			=> 'Verwalte Subscription Pakete',
	'ACP_GROUPSUB_MANAGE_PKGS_EXPLAIN'			=> 'Hier kannst du die verfügbaren Subscription Pakete verwalten.',
	'ACP_GROUPSUB_NO_PKGS'						=> 'Keine Subscription Pakete',
	'ACP_GROUPSUB_PKG_ADD'						=> 'Erstelle Subscription Paket',
	'ACP_GROUPSUB_PKG_ADD_SUCCESS'				=> 'Subscription Paket erfolgreich erstellt.',
	'ACP_GROUPSUB_PKG_EDIT'						=> 'Bearbeite Subscription Paket',
	'ACP_GROUPSUB_PKG_EDIT_SUCCESS'				=> 'Subscription Paket Details erfolgreich gespeichert',
	'ACP_GROUPSUB_PKG_DELETE_CONFIRM'			=> 'Bist du sicher, dass du dieses Subscription Paket löschen möchtest?',
	'ACP_GROUPSUB_PKG_DELETE_SUCCESS'			=> 'Paket erfolgreich gelöscht',
	'ACP_GROUPSUB_PKG_DETAILS'					=> 'Paket Details',
	'ACP_GROUPSUB_PKG_ENABLE'					=> 'Aktiviere Paket',
	'ACP_GROUPSUB_PKG_ENABLE_EXPLAIN'			=> 'Dieses Paket den Benutzern verfügbar machen.',
	'ACP_GROUPSUB_PKG_IDENT'					=> 'Paket Bezeichner',
	'ACP_GROUPSUB_PKG_IDENT_EXPLAIN'			=> 'Eine eindeutige Zeichenkette zur Identifikation des Pakets. Der Wert darf nur a-z, 0-9, _, enhtalten udn muss mit einem Buchstaben beginnen.',
	'ACP_GROUPSUB_PKG_NAME'						=> 'Paket Name',
	'ACP_GROUPSUB_PKG_DESC'						=> 'Paket Beschreibung',
	'ACP_GROUPSUB_PKG_START'					=> 'Subscription Start-Aktion',
	'ACP_GROUPSUB_PKG_END'						=> 'Subscription Beendigungs-Aktion',
	'ACP_GROUPSUB_PKG_GROUPS_ADD'				=> 'Füge Subscriber zur Gruppe hinzu',
	'ACP_GROUPSUB_PKG_GROUPS_ADD_EXPLAIN'		=> 'Die Subscriber werden den hier ausgewählten Gruppen hinzugefügt.',
	'ACP_GROUPSUB_PKG_GROUPS_REMOVE'			=> 'Entferne Subscriber aus Gruppen',
	'ACP_GROUPSUB_PKG_GROUPS_REMOVE_EXPLAIN'	=> 'Die Subscriber werden aus den hier ausgewählten Gruppen entfernt. Die Subscriber werden <strong>nicht</strong> entfernt, wenn sie eine andere aktive Subscription zu der Gruppe haben.',
	'ACP_GROUPSUB_PKG_DEFAULT_GROUP'			=> 'Setze Standard Gruppe',
	'ACP_GROUPSUB_PKG_DEFAULT_GROUP_EXPLAIN'	=> 'Die Standard Gruppe des Subscriber wird auf die hier gewählte gruppe gesetzt.',
	'ACP_GROUPSUB_PKG_TERM_ADD'					=> 'Dauer hinzufügen',
	'ACP_GROUPSUB_PKG_TERMS'					=> 'Subscription Dauer',
	'ACP_GROUPSUB_PKG_PRICE'					=> 'Subscription Preis',
	'ACP_GROUPSUB_PKG_PRICE_EXPLAIN'			=> 'Gib den Preis für die Subscription an.',
	'ACP_GROUPSUB_PKG_LENGTH'					=> 'Subscription Dauer',
	'ACP_GROUPSUB_PKG_LENGTH_EXPLAIN'			=> 'Gib die Dauer der Subscription an. Benutze 0 für eine unbefristete Subscription.',

	'ACP_GROUPSUB_NAME'		=> 'Name',
	'ACP_GROUPSUB_TERMS'	=> 'Dauer',
	'ACP_GROUPSUB_PRICE'	=> 'Preis',
	'ACP_GROUPSUB_LENGTH'	=> 'Länge',
	'ACP_GROUPSUB_MORE'		=> '+%d mehr…',

	'ACP_GROUPSUB_EXPIRES_UNLIMITED'	=> 'Unbegrenzt',

	'ACP_GROUPSUB_ERROR_MISSING_TERMS'	=> 'Ein Paket muss eine Dauer haben, um aktiviert werden zu können.',
));
