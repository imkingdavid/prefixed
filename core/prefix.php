<?php

class phpbb_ext_imkingdavid_prefixed_core_prefix
{
	/**
	 * Database
	 * @var dbal
	 */
	private $db;

	/**
	 * Cache
	 * @var phpbb_cache_service
	 */
	private $cache;

	/**
	 * Prefix ID
	 * @var int
	 */
	private $id;

	/**
	 * Prefix Title
	 * @var string
	 */
	private $title;

	/**
	 * Prefix Short name
	 * @var string
	 */
	private $short;

	/**
	 * Prefix style (serialized)
	 * @var string
	 */
	private $style;

	/**
	 * Prefix allowed users
	 * @var string
	 */
	private $users;

	/**
	 * Prefix allowed forums
	 * @var string
	 */
	private $forums;

	/**
	 * Serialized token array
	 * @var string
	 */
	private $token_data;

	/**
	 * Constructor method
	 */
	public function __construct(dbal $db, phpbb_cache_service $cache, $id = 0)
	{
		$this->id = $id;
		$this->db = $db;
		$this->cache = $cache;

		if ($this->id)
		{
			$this->load();
		}
	}

	public function load()
	{
		if (!$this->id)
		{
			return false;
		}
		
		// If this particular prefix is in the cache, we can grab it there
		// Otherwise, we just query for it
		if ((($prefix = $this->cache->get('_prefixes')) === false) || empty($prefix[$this->id]))
		{
			$sql = 'SELECT title, short, color, users, forums
				FROM ' . PREFIXES_TABLE . '
				WHERE id = ' . (int) $this->id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			
			// since the cache is either completely empty
			// or else we dont' have this prefix cached, we need to cache it
			$prefix[$this->id] = $row;
			$this->cache->put('_prefixes', $prefix);
		}
		else
		{
			// if we have the prefix  cached, we can grab it.
			$row = $prefix[$this->id];
		}

		// If after checking the cache and the database we come up empty,
		// we should stop here
		if (empty($row))
		{
			return false;
		}

		// And now we set our class properties
		$this->title = $row['title'];
		$this->short = $row['short'];
		$this->style = $row['style'];
		$this->users = $row['users'];
		$this->forums = $row['forums'];

		return true;
	}

	/**
	 * Set properties
	 *
	 * @param string $property Which property to modify
	 * @param mixed $value What value to assign to the property
	 * @return null
	 */
	public function set($property, $value)
	{
		$this->$property = $value;
	}

	/**
	 * Get a property's value
	 *
	 * @param string $property The property to get
	 * @return mixed Value of the property, null if !isset($property)
	 */
	public function get($property)
	{
		return $this->$property;
	}
}
