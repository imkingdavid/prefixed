<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2013 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace imkingdavid\prefixed\acp;

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
class prefixed_info
{
	function module()
	{
		return [
			'filename'	=> '\imkingdavid\prefixed\acp\prefixed_module',
			'title'		=> 'ACP_PREFIXED_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> [
				'settings'		=> ['title' => 'ACP_PREFIXED_SETTINGS', 'auth' => '', 'cat' => ['ACP_MESSAGES']],
				'prefixes'		=> ['title' => 'ACP_PREFIXED_MANAGE', 'auth' => '', 'cat' => ['ACP_MESSAGES']],
			],
		];
	}
}
