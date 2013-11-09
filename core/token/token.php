<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2013 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace imkingdavid\prefixed\core\token;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

abstract class token implements token_interface
{
	/**
	 * Constructor to provide dependencies to the tokens
	 *
	 * @param \phpbb\user				$user
	 * @param \phpbb\auth\auth			$auth
	 * @param \phpbb\config\config		$config
	 * @param \phpbb\template\template	$template
	 * @param \phpbb\db\driver\driver	$db
	 * @param string					$table_prefix
	 * @return null
	 */
	public function __construct(\phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\db\driver\driver $db, $table_prefix)
	{
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->template = $template;
		$this->db = $db;
	}
}
