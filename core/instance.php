<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2013 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace imkingdavid\prefixed\core;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

class instance extends ArrayObject
{
	use loadable {
		load as _load;
	}

	/**
	 * Database
	 * @var \phpbb\db\driver
	 */
	protected $db;

	/**
	 * Cache
	 * @var \phpbb\cache\service
	 */
	protected $cache;

	/**
	 * Template
	 * @var \phpbb\template
	 */
	protected $template;

	/**
	 * Prefix object instance
	 * @var instance
	 */
	protected $prefix_object = null;

	/**
	 * Constructor method
	 *
	 * @param \phpbb\db\driver $db DBAL object
	 * @param \phpbb\cache\driver_interface $cache Cache driver object
	 * @param \phpbb\template $template Template object
	 * @param int $id Instance ID
	 */
	public function __construct(\phpbb\db\driver\driver $db, \phpbb\cache\driver\driver_interface $cache, \phpbb\template\template $template, $id = 0)
	{
		parent::__construct();

		$this['id'] = $id;
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;

		if ($this['id'])
		{
			$this->load();
		}
	}

	/**
	 * Set the prefix object for this instance
	 *
	 * @param prefix $prefix
	 * @return $this
	 */
	public function set_prefix_object(prefix $prefix)
	{
		$this->prefix_object = $prefix;

		return $this;
	}

	/**
	 * Parse a prefix instance
	 *
	 * @param	string	$block		If given, this name will be passed to
	 *								assign_block_vars (otherwise the variables
	 *								are assigned to the template globally)
	 * @return	 string				Plaintext prefix
	 */
	public function parse($block = '')
	{
		if (!$this->prefix_object instanceof prefix)
		{
			$this->set_prefix_object(new prefix($this->db, $this->cache, $this->template, $this['prefix']));
		}

		if (!$this->prefix_object->loaded() && !$this->prefix_object->load())
		{
			return '';
		}

		foreach (json_decode($this['token_data'], true) as $token => $data)
		{
			$this->prefix_object['title'] = str_replace('{' . $token . '}', $data, $this->prefix_object['title']);
		}

		// To clarify, the second argument here is simply replacing the prefix
		// ID with the instance ID in the template
		return $this->prefix_object->parse($block, ['ID' => $this['id']]);
	}

	/**
	 * Load a prefix instance's data
	 * Sets the internally stored ArrayObject storage array
	 *
	 * @return bool True if the instance exists, false if it doesn't
	 */
	public function load()
	{
		if ($this->loaded())
		{
			return true;
		}
		else if (!$this['id'])
		{
			return false;
		}

		$sql = 'SELECT id, prefix, topic, token_data, ordered
			FROM ' . PREFIX_INSTANCES_TABLE . '
			WHERE id = ' . (int) $this['id'];

		// Uses the load() method of the 'loadable' trait, aliased as _load()
		$row = $this->_load('_prefixes_used', $this['id'], $sql);
		foreach ($row as $key => $value)
		{
			$this[$key] = $value;
		}

		return true;
	}
}
