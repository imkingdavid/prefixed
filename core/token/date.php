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

class date extends token
{
	const DEFAULT_DATE_FORMAT = 'm/d/Y';
	/**
	 * @inheritdoc
	 */
	public function get_token_description_lang()
	{
		return 'PREFIXED_TOKEN_DATE';
	}

	/**
	 * @inheritdoc
	 */
	public function get_token_data($prefix_text, $topic_id, $prefix_id, $forum_id)
	{
		// Allow date format to be passed
		// {DATE} is defaults to: m/d/Y
		if (!preg_match('/{DATE(\|[a-zA-Z-\/\.]+)?}/', $prefix_text, $matches)) {
			return false;
		}

		// if the token is {DATE|m/d/y}
		// $matches[1] will contain the string '|m/d/y'
		// We want to remove the | and use the rest in the date() function
		$format = (sizeof($matches) > 1) ? ltrim($matches[1], '|') : self::DEFAULT_DATE_FORMAT;

		$date = date($format);

		return [
			$this->user->lang[$this->get_token_description_lang()],
			reset($matches),
		];
	}
}
