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
	'GROUPSUB_NO_PACKAGES'		=> 'Es sind keine Subscriptions verfügbar.',
	'GROUPSUB_NO_DESC'			=> 'Keine Beschreibung verfügbar.',
	'GROUPSUB_SUBSCRIPTION'		=> 'Subscription',
	'GROUPSUB_PRICE'			=> 'Preis',
	'GROUPSUB_LENGTH'			=> 'Länge',
	'GROUPSUB_LENGTH_UNLIMITED'	=> 'Unbegrenzt',
	'GROUPSUB_SUBSCRIBE'		=> 'Abonnieren',
	'GROUPSUB_RENEW'			=> 'Erneuere Subscription',
	'GROUPSUB_CHOOSE_TERM'		=> 'Abonniere %s',
	'GROUPSUB_SUBSCRIBED'		=> 'Dein Abonnement läuft unbegrenzt',
	'GROUPSUB_SUBSCRIBED_UNTIL'	=> 'Dein Abonnement läuft bis %s',
	'GROUPSUB_CONFIRM'			=> 'Bestätige Abonnement für %s',

	'GROUPSUB_RETURN_TITLE'		=> 'Vielen Dank',
	'GROUPSUB_RETURN'			=> 'Abonniert',
	'GROUPSUB_RETURN_UNLIMITED'	=> '<strong>unbegrenzt</strong>',
	'GROUPSUB_RETURN_MESSAGE'	=> 'Du hast <strong>%1$s</strong> abonniert für %2$s. Bitte habe ein wenig Geduld, bis die Zahlung abgeschlossen und die Subscription aktiviert wird.',

	'GROUPSUB_PP_LOCALE'	=> 'de_DE',
	'GROUPSUB_PP_BUY_NOW'	=> 'Jetzt kaufen',

	'GROUPSUB_DECIMAL_SEPARATOR'	=> ',',
	'GROUPSUB_THOUSANDS_SEPARATOR'	=> '.',

	'GROUPSUB_DAYS'		=> array(
		1	=> 'Tag',
		2	=> 'Tage',
	),
	'GROUPSUB_WEEKS'	=> array(
		1	=> 'Woche',
		2	=> 'Wochen',
	),
	'GROUPSUB_MONTHS'	=> array(
		1	=> 'Monat',
		2	=> 'Monate',
	),
	'GROUPSUB_YEARS'	=> array(
		1	=> 'Jahr',
		2	=> 'Jahre',
	),
));
