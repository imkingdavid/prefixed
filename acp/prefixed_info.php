<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
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
