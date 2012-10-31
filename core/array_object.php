<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2012 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

trait phpbb_ext_imkingdavid_prefixed_core_array_object
{
	/**
	 * Alter the offsetSet() method to allow disallowance of empty values
	 *
	 * @param string $offset
	 * @param mixed $value
	 * @param bool $allow_empty Whether to allow (true) or disallow (false)
	 *				an empty value for the given offset.
	 * @return null
	 */
	public function offsetSet($offset, $value, $allow_empty = true)
	{
		if (!$allow_empty && empty($value))
		{
			return;
		}

		parent::offsetSet($offset, $value);
	}
}
