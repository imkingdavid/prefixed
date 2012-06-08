<?php

class phpbb_ext_imkingdavid_prefixed_core_instance
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
	private $prefix;

	/**
	 * Prefix object instance
	 * @var phpbb_ext_imkingdavid_prefixed_core_instance
	 */
	private $prefix_obj;

	/**
	 * Topic ID
	 * @var string
	 */
	private $topic;

	/**
	 * Time when the prefix instance was created
	 * @var int
	 */
	private $applied_time;
	
	/**
	 * Order of the prefix
	 * @var int
	 */
	private $ordered;

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

	/**
	 * Parse a prefix instance
	 *
	 * @return mixed False upon failure, otherwise, string containing HTML of parsed prefix
	 */
	public function parse()
	{
		if (!$this->id)
		{
			return false;
		}



		$this->prefix_obj = new phpbb_ext_imkingdavid_prefixed_core_prefix($this->db, $this->cache, $this->prefix);
		$this->prefix_obj->load();
		if (!$this->prefix_obj->get('id'))
		{
			return false;
		}

		$return_string = '<span';
		$return_string .= '>';
		$return_string .= $this->prefix_obj->get('title');
		$return_string .= '</span>';

		return $return_string;
	}

	/**
	 * Load a prefix instance's data
	 *
	 * @return bool True if the instance exists, false if it doesn't
	 */
	public function load()
	{
		if (!$this->id)
		{
			return false;
		}
		
		// If this particular prefix instance is in the cache, we can grab it there
		// Otherwise, we just query for it
		if ((($prefix = $this->cache->get('_prefixes_used')) === false) || empty($prefix[$this->id]))
		{
			$sql = 'SELECT id, prefix, topic, applied_time, applied_user, ordered
				FROM ' . PREFIXES_USED_TABLE . '
				WHERE id = ' . (int) $this->id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			
			// since the cache is either completely empty
			// or else we dont' have this prefix instance cached, we need to cache it
			$prefix[$this->id] = $row;
			$this->cache->put('_prefixes_used', $prefix);
		}
		else
		{
			// if we have the prefix instance cached, we can grab it.
			$row = $prefix[$this->id];
		}

		// If after checking the cache and the database we come up empty,
		// we should stop here
		if (empty($row))
		{
			return false;
		}

		// And now we set our class properties
		$this->prefix = (int) $row['prefix'];
		$this->topic = $row['topic'];
		$this->token_data = $row['token_data'];
		$this->tokens = unserialize($this->token_data);
		$this->applied_time = $row['applied_time'];
		$this->ordered = $row['ordered'];

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
