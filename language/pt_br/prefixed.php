<?php
/**
 *
 * prefixed [Brazilian Portuguese [pt_br]]
 * Brazilian Portuguese translation by eunaumtenhoid (c) 2018 [ver 1.0.0] (https://github.com/phpBBTraducoes)
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
	$lang = [];
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
	'POSTING_PREFIXES'	=> 'Prefixos do tópico',
	'POSTING_PREFIXES_USED' => 'Prefixos de tópicos aplicados',
	'NO_PREFIX' => 'sem prefixo',

	'PREFIXED_TOKEN_USERNAME'			=> '{USERNAME}',
	'PREFIXED_TOKEN_USERNAME_EXPLAIN'	=> 'Este token é substituído pelo nome de usuário do usuário que aplica o prefixo ao tópico.',
	'PREFIXED_TOKEN_POSTER'				=> '{POSTER}',
	'PREFIXED_TOKEN_POSTER_EXPLAIN'		=> 'Este token é substituído pelo nome de usuário do autor do tópico.',
	'PREFIXED_TOKEN_DATE'				=> '{DATE}',
	'PREFIXED_TOKEN_DATE_EXPLAIN'		=> 'Este token é substituído pela data em que o prefixo foi aplicado ao tópico.',
]);
