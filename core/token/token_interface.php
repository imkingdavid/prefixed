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

interface token_interface
{
	/**
	 * In the ACP we will show a list of available tokens. You need two
	 * language variables: one for the most basic form of the token, and
	 * one that explains what the token is used for. The second key should be
	 * the same as the first with _EXPLAIN appended.
	 *
	 * NOTE: Other extensions will need to use phpbb\user::add_lang_ext() to
	 * add the appropriate language file.
	 *
	 * @return string The language key for the description
	 */
	public function get_token_description_lang();

	/**
	 * See if the token exists in the the prefix
	 *
	 * @param string $prefix_text
	 * @return array|bool False if it doesn't match, otherwise the matches
	 * array from preg_match()
	 */
	public function match_token($prefix_text);

	/**
	 * Provide data to prefix instance when it is added to a topic
	 *
	 * @param string The prefix text in which to parse the token
	 * @param int $topic_id ID of the topic to which prefix is being applied
	 * @param int $prefix_id ID of the prefix being applied to the topic
	 * @param int $forum_id ID of the forum containing the topic
	 * @return array|bool False if the pattern doesn't match, otherwise an
	 * array of [token_service_name => data]
	 */
	public function get_token_data($prefix_text, $topic_id, $prefix_id, $forum_id);

	/**
	 * Perform the replacement at runtime given the prefix, the token, and the
	 * replacement
	 *
	 * @param string $prefix_text The whole prefix
	 * @param string $data The data stored with the specific instance
	 * @return string The prefix with the token replaced with the data
	 */
	public function apply_token_data($prefix_text, $data);
}
