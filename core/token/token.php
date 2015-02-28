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
	 * @param \phpbb\db\driver\factory	$db
	 * @param string					$table_prefix
	 * @return null
	 */
	public function __construct(\phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\db\driver\factory $db, $table_prefix)
	{
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->template = $template;
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function get_token_description_lang()
	{
		return 'PREFIXED_TOKEN_' . strtoupper(basename(get_class()));
	}

	/**
	 * @inheritdoc
	 */
	public function match_token($prefix_text)
	{
		$matches = array();
		preg_match(constant(get_class($this) . '::TOKEN_REGEX'), $prefix_text, $matches);
		return $matches ?: false;
	}

	/**
	 * @inheritdoc
	 */
	public function apply_token_data($prefix_text, $data)
	{
		if (($matches = $this->match_token($prefix_text)) === false)
		{
			return false;
		}

		return preg_replace(constant(get_class($this) . '::TOKEN_REGEX'), $data, $prefix_text);
	}
}
