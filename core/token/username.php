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

class username extends token
{
	/**
	 * @inheritdoc
	 */
	public function get_token_description_lang()
	{
		return 'PREFIXED_TOKEN_USERNAME';
	}

	/**
	 * @inheritdoc
	 */
	public function get_token_data($prefix_text, $topic_id, $prefix_id, $forum_id)
	{
		// The current user is the one who applied the prefix
		return preg_replace('/{USERNAME}/', $this->user->data['username'], $prefix_text);
	}
}
