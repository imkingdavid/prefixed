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
	'ACP_PREFIXED_MANAGEMENT'		=> 'Topic Prefix Management',
	'ACP_PREFIXED_MANAGE'			=> 'Manage Prefixes',
	'ACP_PREFIXED_MANAGE_EXPLAIN'	=> 'On this page, you can manage the topic prefixes for your board.',

	'ACP_PREFIXES'			=> 'Topic Prefixes',
	'PREFIX'				=> 'Prefix',
	'PREFIX_PARSED'			=> 'Output',
	'ADD_PREFIX'			=> 'New Prefix',

	'PREFIX_TITLE'			=> 'Prefix',
	'PREFIX_TITLE_EXPLAIN'	=> 'This is what will actually be displayed in front of the topic title. BBCode is supported. Certain tokens may be used, which will be substituted with actual data when the prefix is applied.',
	'PREFIX_SHORT'			=> 'Short name',
	'PREFIX_SHORT_EXPLAIN'	=> 'This is a unique identifier to help you differentiate between prefixes.',
	'PREFIX_FORUMS'			=> 'Forums',
	'PREFIX_FORUMS_EXPLAIN'	=> 'Specify which forums can contain this prefix.',
	'PREFIX_GROUPS'			=> 'Groups',
	'PREFIX_GROUPS_EXPLAIN'	=> 'Specify which groups can apply this prefix.',
	'PREFIX_USERS'			=> 'Users',
	'PREFIX_USERS_EXPLAIN'	=> 'Specify which users can apply this prefix (overrides group setting).',

	'DELETE_PREFIX'				=> 'Are you sure you want to delete the specified prefix?',
	'DELETE_PREFIX_CONFIRM'		=> 'The prefix and all of its instances will be deleted. This cannot be undone.',

	'PREFIX_ADDED_SUCCESS'		=> 'The prefix has been added successfully.',
	'PREFIX_EDITED_SUCCESS'		=> 'The prefix has been updated successfully.',
	'PREFIX_DELETED_SUCCESS'	=> 'The prefix has been deleted successfully.',
	'NO_PREFIX_ID_SPECIFIED'	=> 'You must specify a valid prefix ID.',
]);
