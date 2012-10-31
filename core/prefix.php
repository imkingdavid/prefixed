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

class phpbb_ext_imkingdavid_prefixed_core_prefix extends ArrayObject
{
	use phpbb_ext_imkingdavid_prefixed_core_array_object,
	phpbb_ext_imkingdavid_prefixed_core_loadable {
		load as loader;
	}

	/**
	 * Database
	 * @var dbal
	 */
	protected $db;

	/**
	 * Cache
	 * @var phpbb_cache_service
	 */
	protected $cache;

	/**
	 * Constructor method
	 *
	 * @param dbal $db Database object
	 * @param phpbb_cache_service $cache Cache object
	 * @param int $id Prefix ID
	 */
	public function __construct(dbal $db, phpbb_cache_driver_interface $cache, $id = 0)
	{
		parent::__construct();

		$this->offsetSet('id', $id, false);
		$this->db = $db;
		$this->cache = $cache;

		if ($this['id'])
		{
			$this->load();
		}
	}

	/**
	 * Load the data about this prefix
	 * Sets the internally stored ArrayObject storage array
	 *
	 * @return bool
	 */
	public function load()
	{
		if ($this->loaded() || !$this['id'])
		{
			return false;
		}

		$sql = 'SELECT id, title, short, style, users, forums
			FROM ' . PREFIXES_TABLE . '
			WHERE id = ' . (int) $this['id'];

		// Uses the load() method of the 'loadable' trait
		$row = $this->loader('_prefixes', $this['id'], $sql);
		foreach ($row as $key => $value)
		{
			$this[$key] = $value;
		}

		return $this->loaded = true;
	}
}
