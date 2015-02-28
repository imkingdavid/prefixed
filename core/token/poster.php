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

class poster extends token
{
	const TOKEN_REGEX = '/{POSTER}/';

	/**
	 * @inheritdoc
	 */
	public function get_token_data($prefix_text, $topic_id, $prefix_id, $forum_id)
	{
		if (false === $this->match_token($prefix_text))
		{
			return false;
		}

		// Get the poster of the topic
		$sql = 'SELECT poster_id
			FROM ' . $this->table_prefix . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$poster_username = $this->db->sql_fetchfield('poster_id');
		$this->db->sql_freeresult();

		if (!$poster_username)
		{
			return false;
		}

		return [
			// We store the service name so that there's no guesswork later
			'service' => 'prefixed.token.poster',
			'data' => $poster_username,
		];
	}
}
