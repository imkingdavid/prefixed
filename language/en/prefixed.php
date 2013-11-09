<?php
/**
 *
 * prefixed [English]
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
	'POSTING_PREFIXES'	=> 'Topic prefixes',
	'POSTING_PREFIXES_USED' => 'Topic prefixes applied',

	'PREFIXED_TOKEN_USERNAME'			=> '{USERNAME}',
	'PREFIXED_TOKEN_USERNAME_EXPLAIN'	=> 'This token is replaced with the username of the user who applies the prefix to the topic.',
	'PREFIXED_TOKEN_POSTER'				=> '{POSTER}',
	'PREFIXED_TOKEN_POSTER_EXPLAIN'		=> 'This token is replaced with the username of the topic poster.',
	'PREFIXED_TOKEN_DATE'				=> '{DATE}',
	'PREFIXED_TOKEN_DATE_EXPLAIN'		=> 'This token is replaced with the date on which the prefix was applied to to the topic.',
]);
