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
	const TOKEN_REGEX = '/{DATE(\|[a-zA-Z-\/\. ]+)?}/';
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
		// @TODO - figure out how to handle if they change the date format
		// any old prefix instances will stop showing the replacement for the token

		// Allow date format to be passed
		// {DATE} is defaults to: m/d/Y
		// Format can be 
		if ($this->match_token($prefix_text) === false)
		{
			return false;
		}

		return [
			// We store the service name so that there's no guesswork later
			'service' => 'prefixed.token.date',
			time(),
		];
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

		// if the token is {DATE|m/d/y}
		// $matches[1] will contain the string '|m/d/y'
		// We want to remove the | and use the rest in the date() function
		$format = (sizeof($matches) > 1) ? ltrim($matches[1], '|') : self::DEFAULT_DATE_FORMAT;
		// $data should contain the timestamp
		return preg_replace(self::TOKEN_REGEX, date($format, $data), $prefix_text);
	}
}
