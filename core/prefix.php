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
	 * @param phpbb_db_driver $db Database object
	 * @param phpbb_cache_service $cache Cache object
	 * @param int $id Prefix ID
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
	 * @return	string	Plaintext prefix
	 */
	public function parse($block = '', array $vars = [], $var_prefix = '')
	{
		if (!$this->loaded() && !$this->load())
		{
			return '';
		}

		$var_prefix = strtoupper($var_prefix);
		$this['title'] = generate_text_for_display($this['title'], $this['bbcode_uid'], $this['bbcode_bitfield'], OPTION_FLAG_BBCODE | OPTION_FLAG_SMILIES);
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

		return $this['title'];
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

		$sql = 'SELECT id, title, short, users, forums, bbcode_uid, bbcode_bitfield
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
	 * Update/Insert an entry in the database
	 *
	 * If $this->id === 0 Then: INSERT; Else: UPDATE; Endif;
	 */
	public function update()
	{

	}

	/**
	 * Set a property or array of properties
	 *
	 * @param array $key_val [key => value] pairs
	 * @return null
	 */
	public function set($key_val)
	{

	}
}
