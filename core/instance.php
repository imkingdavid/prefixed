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
	 * Instance ID
	 * @var int
	 */
	private $instance_id;

	/**
	 * Prefix ID
	 * @var int
	 */
	private $prefix_id;

	/**
	 * Prefix object instance
	 * @var phpbb_ext_imkingdavid_prefixed_core_instance
	 */
	private $prefix_object;

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
	 * Token data (serialized array)
	 * @var string
	 */
	private $token_data;

	/**
	 * Constructor method
	 */
	public function __construct(dbal $db, phpbb_cache_driver_interface $cache, phpbb_template $template, $instance_id = 0)
	{
		$this->instance_id = $instance_id;
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;

		if ($this->instance_id)
		{
			$this->load();
		}
	}

	/**
	 * Parse a prefix instance
	 *
	 * @param	string	$block		If given, this name will be passed to
	 *								assign_block_vars (otherwise the variables
	 *								are assigned to the template globally)
	 * @return	bool|string			False on failure; otherwise string
	 *								containing the plaintext version of prefix
	 */
	public function parse($block = '')
	{
		$this->prefix_object = new phpbb_ext_imkingdavid_prefixed_core_prefix($this->db, $this->cache, $this->prefix_id);

		if (!$this->prefix_object->get('loaded') && !$this->prefix_object->load())
		{
			return false;
		}

		$title = $this->prefix_object->get('title');
		$tokens = json_decode($this->token_data);
		$style = json_decode($this->prefix_object->get('style'));

		foreach ($tokens as $token => $data)
		{
			$title = str_replace('{' . $token . '}', $data, $title);
		}

		$css_string = '';
		foreach ($style as $attribute => $value)
		{
			$css_string .= $attribute . ': ' . $value . ';';
		}

		$tpl_vars = [
			'SHORT'	=> $this->prefix_object->get('short'),
			'TITLE'	=> $title,
			'STYLE'	=> $css_string,
		];

		if ($block)
		{
			$this->template->assign_block_vars($block, $tpl_vars);
		}
		else
		{
			$this->template->assign_vars($tpl_vars);
		}

		return $title;
	}

	/**
	 * Load a prefix instance's data
	 *
	 * @return bool True if the instance exists, false if it doesn't
	 */
	public function load()
	{
		if (!$this->instance_id)
		{
			return false;
		}

		// If this particular prefix instance is in the cache, we can grab it there
		// Otherwise, we just query for it
		if ((($prefix = $this->cache->get('_prefixes_used')) === false) || empty($prefix[$this->instance_id]))
		{
			$sql = 'SELECT id, prefix, topic, token_data, ordered
				FROM ' . PREFIX_INSTANCES_TABLE . '
				WHERE id = ' . (int) $this->instance_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			// since the cache is either completely empty
			// or else we dont' have this prefix instance cached, we need to cache it
			$prefix[$this->instance_id] = $row;
			$this->cache->put('_prefixes_used', $prefix);
		}
		else
		{
			// if we have the prefix instance cached, we can grab it.
			$row = $prefix[$this->instance_id];
		}

		// If after checking the cache and the database we come up empty,
		// we should stop here
		if (empty($row))
		{
			return false;
		}

		// And now we set our class properties
		$this->prefix_id = (int) $row['prefix'];
		$this->topic = $row['topic'];
		$this->token_data = $row['token_data'];
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
		$property = strtolower($property);
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
		$property = strtolower($property);
		return $this->$property;
	}
}
