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

class prefix extends \ArrayObject
{
	use loadable {
		load as _load;
	}

	/**
	 * Database
	 * @var phpbb_db_driver
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
	 * Constructor method
	 *
	 * @param \phpbb\db\driver\factory $db Database object
	 * @param phpbb_cache_service $cache Cache object
	 * @param int $id Prefix ID
	 */
	public function __construct(\phpbb\db\driver\factory $db, \phpbb\cache\driver\driver_interface $cache, \phpbb\template\template $template, $id = 0)
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
	 * This function assigns the parsed prefix to to template variables and
	 * returns the plain text prefix (used for browser page title)
	 *
	 * @param	string	$block		If given, this name will be passed to
	 *								assign_block_vars (otherwise the variables
	 *								are assigned to the template globally)
	 * @param	string	$vars		Variables to send to the template
	 * @param	string  $var_prefix Optional prefix for template variables
	 *								This only applies to the four vars that
	 *								are assigned in this method
	 * @param   bool    $return_parsed If true, return is the parsed prefix
	 *								otherwise, it is the plaintext version
	 * @return	string	Plaintext prefix
	 */
	public function parse($block = '', array $vars = [], $var_prefix = '', $return_parsed = false)
	{
		if (!$this->loaded() && !$this->load())
		{
			return '';
		}

		$var_prefix = strtoupper($var_prefix);
		$this['title'] = generate_text_for_display($this['title'], $this['bbcode_uid'], $this['bbcode_bitfield'], OPTION_FLAG_BBCODE);
		$tpl_vars = array_merge([
			$var_prefix . 'ID'		=> $this['id'],
			$var_prefix . 'SHORT'	=> $this['short'],
			$var_prefix . 'TITLE'	=> $this['title'],
		], $vars);

		call_user_func_array(
			[
				$this->template,
				$block ? 'assign_block_vars' : 'assign_vars',
			],
			$block ? [$block, $tpl_vars] : [$tpl_vars]
		);

		return true === $return_parsed ? $this['title'] : strip_tags($this['title']);
	}

	/**
	 * Load the data about this prefix
	 * Sets the internally stored ArrayObject storage array
	 *
	 * @return bool
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

		// This SQL is only run if the prefix is not cached
		$sql = 'SELECT id, title, short, users, forums, groups, bbcode_uid, bbcode_bitfield
			FROM ' . PREFIXES_TABLE . '
			WHERE id = ' . (int) $this['id'];

		// Uses the load() method of the 'loadable' trait, aliased as _load()
		$row = $this->_load('_prefixes', $this['id'], $sql);
		foreach ($row as $key => $value)
		{
			$this[$key] = $value;
		}

		return true;
	}

	/**
	 * Set array of properties
	 *
	 * @param array $data Array of [key => value] pairs
	 * @return null
	 */
	public function set_data($data)
	{
		foreach ($data as $key => $value)
		{
			$this[$key] = $value;
		}
	}
}
