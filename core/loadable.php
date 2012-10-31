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

trait phpbb_ext_imkingdavid_prefixed_core_loadable
{
	/**
	 * Whether or not the object has been loaded
	 * @var bool
	 */
	protected $loaded = false;

	/**
	 * Load a loadable item
	 * The class that uses this should have $this->cache and $this->db defined
	 * properly.
	 *
	 * @param string $cache_name Name of the cache item to check for
	 * @param int $id The ID of the given item
	 * @param string $sql The query string to run
	 * @return array Data from either the cache or database
	 */
	public function load($cache_name, $id, $sql)
	{
		if ((($prefix = $this->cache->get($cache_name)) === false) || empty($prefix[$id]))
		{
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			// since the cache is either completely empty
			// or else we dont' have this prefix instance cached, we need to cache it
			$prefix[$id] = $row;
			$this->cache->put($cache_name, $prefix);
		}
		else
		{
			// if we have the prefix instance cached, we can grab it.
			$row = $prefix[$id];
		}

		// If after checking the cache and the database we come up empty,
		// we should stop here
		if (empty($row))
		{
			return array();
		}

		return $row;
	}

	/**
	 * Determine whether the prefix has been loaded
	 *
	 * @return bool
	 */
	public function loaded()
	{
		return $this->loaded;
	}
}
