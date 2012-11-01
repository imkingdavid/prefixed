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

class phpbb_ext_imkingdavid_prefixed_core_instance extends ArrayObject
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
	 * Template
	 * @var phpbb_template
	 */
	protected $template;

	/**
	 * Prefix object instance
	 * @var phpbb_ext_imkingdavid_prefixed_core_instance
	 */
	protected $prefix_object = null;

	/**
	 * Constructor method
	 *
	 * @param dbal $db DBAL object
	 * @param phpbb_cache_driver_interface $cache Cache driver object
	 * @param phpbb_template $template Template object
	 * @param int $id Instance ID
	 */
	public function __construct(dbal $db, phpbb_cache_driver_interface $cache, phpbb_template $template, $id = 0)
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
	 * @param phpbb_ext_imkingdavid_prefixed_core_prefix
	 * @return $this
	 */
	public function set_prefix_object(phpbb_ext_imkingdavid_prefixed_core_prefix $prefix)
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
		if (!$this->prefix_object instanceof phpbb_ext_imkingdavid_prefixed_core_prefix)
		{
			$this->set_prefix_object(new phpbb_ext_imkingdavid_prefixed_core_prefix($this->db, $this->cache, $this->template, $this['prefix']));
		}

		if (!$this->prefix_object->loaded() && !$this->prefix_object->load())
		{
			return '';
		}

		foreach (json_decode($this['token_data'], true) as $token => $data)
		{
			$this->prefix_object['title'] = str_replace('{' . $token . '}', $data, $this->prefix_object['title']);
		}

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

		// Uses the load() method of the 'loadable' trait
		$row = $this->loader('_prefixes_used', $this['id'], $sql);
		foreach ($row as $key => $value)
		{
			$this[$key] = $value;
		}

		return true;
	}
}
