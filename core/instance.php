<?php

class phpbb_ext_imkingdavid_prefixed_core_instance
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
	 * @var int Prefix ID
	 */
	private $prefix;

	/**
	 * @var phpbb_ext_imkingdavid_prefixed_core_instance Prefix object instance
	 */
	private $prefix_obj;
	/**
	 * @var string Topic ID
	 */
	private $topic;
	/**
	 * @var string Serialized token array
	 */
	private $token_data;
	/**
	 * @var array Token array
	 */
	private $tokens;
	/**
	 * @var int Time when the prefix instance was created
	 */
	private $applied_time;
	/**
	 * @var int Order of the prefix
	 */
	private $ordered;

	/**
	 * Constructor method
	 */
	public function __construct(dbal $db, acm $cache, $id = 0)
	{
		$this->set('id', $id);
		$this->set('db', $db);
		$this->set('cache', $cache);
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

		$this->set('prefix_obj', new phpbb_ext_imkingdavid_prefixed_core_prefix($this->db, $this->cache, $this->prefix));
		$this->prefix_obj->load();
		if (!$this->prefix_obj->id)
		{
			return false;
		}

		$return_string = '<span';
		// Don't worry, I will only allow a certain few style things to be used
		// And I will validate the user input so there isn't some wacky injection or anything
		if ($this->prefix_obj->style)
		{
			$style = unserialize($this->prefix_obj->style);
			$return_string .= ' style="'
			foreach ($style as $attr => $value)
			{
				$return_string .= "$attr:'$value';";
			}
			$return_string .= '"';
		}

		$return_string .= '>';
		$return_string .= $this->prefix_obj->title;
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
			$sql = 'SELECT prefix, topic, token_data, ordered
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
		$this->set('prefix', (int) $row['prefix']);
		$this->set('topic', $row['topic']);
		$this->set('token_data', $row['token_data']);
		$this->set('tokens', unserialize($this->token_data));
		$this->set('applied_time', $row['applied_time']);
		$this->set('ordered', $row['ordered']);

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
