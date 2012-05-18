<?php

class phpbb_ext_imkingdavid_prefixed_core_prefix
{
	/**
	 * @var dbal Database
	 */
	private $db;
	/**
	 * @var acm Cache
	 */
	private $cache;
	/**
	 * @var int ID
	 */
	private $id;
	/**
	 * @var string Title
	 */
	private $title;
	/**
	 * @var string Short name
	 */
	private $short;
	/**
	 * @var string Style (Serialized)
	 */
	private $style;
	/**
	 * @var string Users
	 */
	private $users;	
	/**
	 * @var string Forums
	 */
	private $forums;

	/**
	 * Constructor method
	 */
	public function __construct(dbal $db, acm $cache, $id = 0)
	{
		$this->set('id', $id);
		$this->set('db', $db);
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
		$this->set('title', $row['title']);
		$this->set('short', $row['short']);
		$this->set('style', $row['style']);
		$this->set('users', $row['users']);
		$this->set('forums', $row['forums']);

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
		// If the property exists let's set it
		if (isset($this->$property))
		{
			$this->$property = $value;
		}
	}

	/**
	 * Get a property's value
	 *
	 * @param string $property The property to get
	 * @return mixed Value of the property, null if !isset($property)
	 */
	public function get($property)
	{
		return (isset($this->$property)) ? $this->$property : null;
	}
}
