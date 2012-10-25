<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2012 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 *
 */
class phpbb_ext_imkingdavid_prefixed_acp_prefixed_info
{
	function module()
	{
		return [
			'filename'	=> 'prefixed_module',
			'title'		=> 'ACP_PREFIXED_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> [
				'settings'		=> ['title' => 'ACP_PREFIXED_SETTINGS', 'auth' => 'acl_a_prefixes', 'cat' => ['ACP_MESSAGES']],
				'prefixes'		=> ['title' => 'ACP_PREFIXED_MANAGE', 'auth' => 'acl_a_prefixes', 'cat' => ['ACP_MESSAGES']],
			],
		];
	}
}
