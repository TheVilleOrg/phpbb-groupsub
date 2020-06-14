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
	'ACP_GROUPSUB_SETTINGS_TITLE'			=> 'Group Subscription Einstellungen',
	'ACP_GROUPSUB_SETTINGS_SAVED'			=> 'Group Subscription Einstellungen erfoglreich gespeichert',
	'ACP_GROUPSUB_SETTINGS_PAYPAL'			=> 'PayPal Einstellungen',
	'ACP_GROUPSUB_PP_SANDBOX'				=> 'Aktiviere Sandbox Modus',
	'ACP_GROUPSUB_PP_SANDBOX_EXPLAIN'		=> 'Sandbox Modus erlaubt dir Paypal Zahlungen zu testen ohne echte Transaktionen zu verursachen.',
	'ACP_GROUPSUB_PP_SB_BUSINESS'			=> 'Sandbox email Adresse',
	'ACP_GROUPSUB_PP_SB_BUSINESS_EXPLAIN'	=> 'Das ist die email Adresse für deinen Paypal Sandbox Account.',
	'ACP_GROUPSUB_PP_BUSINESS'				=> 'PayPal email Adresse',
	'ACP_GROUPSUB_PP_BUSINESS_EXPLAIN'		=> 'Das ist die email Adresse für deinen Paypal Account, der die Zahlungen entgegennimmt',
	'ACP_GROUPSUB_SETTINGS_GENERAL'			=> 'Allgemeine Einstellungen',
	'ACP_GROUPSUB_NOTIFY_ADMINS'			=> 'Informiere Admins',
	'ACP_GROUPSUB_NOTIFY_ADMINS_EXPLAIN'	=> 'Wenn aktivieret, werden Administratoren mit <em>“Can view users’ subscriptions”</em>-Berechtigung über alle neuen Subscriptions informiert.',
	'ACP_GROUPSUB_HEADER'					=> 'Kopfzeile',
	'ACP_GROUPSUB_HEADER_EXPLAIN'			=> 'Information, die auf allen Subscription Seiten oben angezeigt wird.',
	'ACP_GROUPSUB_FOOTER'					=> 'Fußzeile',
	'ACP_GROUPSUB_FOOTER_EXPLAIN'			=> 'Information, die auf allen Subscription Seiten unten angezeigt wird.',
	'ACP_GROUPSUB_COLLAPSE_TERMS'			=> 'Liste mit Dauern zusammenklappen',
	'ACP_GROUPSUB_COLLAPSE_TERMS_EXPLAIN'	=> 'Die Liste der Dauern für ein Paket wird in einer Select Box angezeigt, wenn mehr als hier angegeben existieren.',
	'ACP_GROUPSUB_SETTINGS_DEFAULTS'		=> 'Paket Standards',
	'ACP_GROUPSUB_DEFAULT_CURRENCY'			=> 'Standard Währung',
	'ACP_GROUPSUB_DEFAULT_CURRENCY_EXPLAIN'	=> 'Dies ist die Standard Währung für alle neuen Pakete, die in den Paketeinstellungen überschrieben werden können.',
	'ACP_GROUPSUB_WARN_TIME'				=> 'Warnzeit',
	'ACP_GROUPSUB_WARN_TIME_EXPLAIN'		=> 'Anzahl an Tagen vor Ablauf der Subscription, zu der der Subscriber informiert wird.',
	'ACP_GROUPSUB_GRACE'					=> 'Schonfrist',
	'ACP_GROUPSUB_GRACE_EXPLAIN'			=> 'Anzahl an Tagen nach Ablauf der Subscription, bevor der Benutzer aus Gruppen entfernt wird.',

	'ACP_GROUPSUB_ERROR_CURRENCY'	=> 'Du musst eine korrekte Währung wählen.',
));
