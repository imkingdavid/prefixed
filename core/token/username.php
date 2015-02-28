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
	const TOKEN_REGEX = '/{USERNAME}/';

	/**
	 * @inheritdoc
	 */
	public function get_token_data($prefix_text, $topic_id, $prefix_id, $forum_id)
	{
		if (false === $this->match_token($prefix_text))
		{
			return false;
		}

		return [
			// We store the service name so that there's no guesswork later
			'service' => 'prefixed.token.username',
			// The current user is the one who applied the prefix
			'data' => $this->user->data['username'],
		];
	}
}
