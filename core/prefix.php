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
	 * @var string Color
	 */
	private $color;
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
	}

	public function load()
	{
		if ($this->id)
		{
			$sql = 'SELECT title, short, color, users, forums
				FROM ' . PREFIXES_TABLE . '
				WHERE id = ' . (int) $this->id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			$this->set('title', $row['title']);
			$this->set('short', $row['short']);
			$this->set('color', $row['color']);
			$this->set('users', $row['users']);
			$this->set('forums', $row['forums']);
		}
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
