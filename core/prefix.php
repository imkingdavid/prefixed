<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2013 David King (imkingdavid)
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

class phpbb_ext_imkingdavid_prefixed_core_prefix extends ArrayObject
{
	use phpbb_ext_imkingdavid_prefixed_core_loadable {
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
	public function __construct(phpbb_db_driver $db, phpbb_cache_driver_interface $cache, phpbb_template $template, $id = 0)
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
	 * @return	string	Plaintext prefix
	 */
	public function parse($block = '', array $vars = [])
	{
		if (!$this->loaded() && !$this->load())
		{
			return '';
		}

		$style = '';
		if (!empty($this['style']))
		{
			foreach (json_decode($this['style'], true) as $attribute => $value)
			{
				$style .= $attribute . ': ' . $value . ';';
			}
		}

		$tpl_vars = array_merge([
			'ID'	=> $this['id'],
			'SHORT'	=> $this['short'],
			'TITLE'	=> $this['title'],
			'STYLE'	=> $style,
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

		$sql = 'SELECT id, title, short, style, users, forums
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
}
