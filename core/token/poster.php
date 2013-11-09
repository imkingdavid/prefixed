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
	/**
	 * @inheritdoc
	 */
	public function get_token_description_lang()
	{
		return 'PREFIXED_TOKEN_POSTER';
	}

	/**
	 * @inheritdoc
	 */
	public function get_token_data($prefix_text, $topic_id, $prefix_id, $forum_id)
	{
		// Get the poster of the topic
		$sql = 'SELECT poster_id
			FROM ' . $this->table_prefix . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$poster_username = $this->db->sql_fetchfield('poster_id');
		$this->db->sql_freeresult();

		return preg_replace('/{POSTER}/', $poster_username, $prefix_text);
	}
}
