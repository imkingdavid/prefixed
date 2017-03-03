<?php
/**
 *
 * prefixed [Swedish]
 * Swedish translation by Holger (http://www.maskinisten.net)
 *
 * @package language
 * @copyright (c) 2005 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

$lang = array_merge($lang, [
	'ACP_PREFIXED_MANAGEMENT'		=> 'Ämnesprefix hantering',
	'ACP_PREFIXED_MANAGE'			=> 'Hantera prefix',
	'ACP_PREFIXED_MANAGE_EXPLAIN'	=> 'Här kan du hantera ämnesprefixen för ditt forum.',

	'ACP_PREFIXES'			=> 'Ämnesprefix',
	'PREFIX'				=> 'Prefix',
	'PREFIX_PARSED'			=> 'Resultat',
	'ADD_PREFIX'			=> 'Nytt prefix',

	'PREFIX_TITLE'			=> 'Prefix',
	'PREFIX_TITLE_EXPLAIN'	=> 'Detta är resultatet som kommer att visas framför ämnets rubrik. BBCode stöds. Speciella platshållare kan användas, dessa platshållare ersätts när prefixet används.',
	'PREFIX_SHORT'			=> 'Kort benämning',
	'PREFIX_SHORT_EXPLAIN'	=> 'Detta är en unik benämning som hjälper dig att hålla reda på prefixen.',
	'PREFIX_FORUMS'			=> 'Forum',
	'PREFIX_FORUMS_EXPLAIN'	=> 'Ange forumen som detta prefix får användas i.',
	'PREFIX_GROUPS'			=> 'Grupper',
	'PREFIX_GROUPS_EXPLAIN'	=> 'Ange grupper som får använda detta prefix.',
	'PREFIX_USERS'			=> 'Användare',
	'PREFIX_USERS_EXPLAIN'	=> 'Ange användare som får använda detta prefix (högre prioritet än grupper).',

	'DELETE_PREFIX'				=> 'Är du säker på att du vill radera det angivna prefixet?',
	'DELETE_PREFIX_CONFIRM'		=> 'Prefixet och alla instanser kommer att raderas. Detta kan ej återställas.',

	'PREFIX_ADDED_SUCCESS'		=> 'Prefixet har lagts till.',
	'PREFIX_EDITED_SUCCESS'		=> 'Prefixet har uppdaterats.',
	'PREFIX_DELETED_SUCCESS'	=> 'Prefixet har raderats.',
	'NO_PREFIX_ID_SPECIFIED'	=> 'Du måste ange ett giltigt prefix-ID.',
]);
