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
	 */
	public function __construct(dbal $db, phpbb_cache_driver_interface $cache, phpbb_template $template, $instance_id = 0)
	{
		parent::__construct();

		$this->offsetSet('id', $instance_id, false);
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;

		if ($this['id'])
		{
			$this->load();
		}
	}

	/**
	 * Set the instance ID
	 *
	 * @param int $id Instance ID
	 * @return $this
	 */
	public function set_id($id = 0)
	{
		if (!empty($id))
		{
			$this['id'] = $id;
		}

		return $this;
	}

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
	 * @return	bool|string			False on failure; otherwise string
	 *								containing the plaintext version of prefix
	 */
	public function parse($block = '')
	{
		if (!$this->prefix_object instanceof phpbb_ext_imkingdavid_prefixed_core_prefix)
		{
			$this->set_prefix_object(new phpbb_ext_imkingdavid_prefixed_core_prefix($this->db, $this->cache, $this['prefix']));
		}

		if (!$this->prefix_object->loaded() && !$this->prefix_object->load())
		{
			return false;
		}

		$title = $this->prefix_object['title'];
		foreach (json_decode($this['token_data'], true) as $token => $data)
		{
			$title = str_replace('{' . $token . '}', $data, $title);
		}

		$style = '';
		foreach (json_decode($this->prefix_object['style'], true) as $attribute => $value)
		{
			$style .= $attribute . ': ' . $value . ';';
		}

		$tpl_vars = [
			'ID'	=> 1,
			'SHORT'	=> $this->prefix_object['short'],
			'TITLE'	=> $title,
			'STYLE'	=> $style,
		];

		call_user_func_array(
			[
				$this->template,
				$block ? 'assign_block_vars' : 'assign_vars',
			],
			$block ? [$block, $tpl_vars] : [$tpl_vars]
		);

		return $title;
	}

	/**
	 * Load a prefix instance's data
	 * Sets the internally stored ArrayObject storage array
	 *
	 * @return bool True if the instance exists, false if it doesn't
	 */
	public function load()
	{
		if ($this->loaded() || !$this['id'])
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

		return $this->loaded = true;
	}
}
