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

class instance extends \ArrayObject
{
	use \imkingdavid\prefixed\core\loadable {
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
	 * Tokens
	 * @var array
	 */
	protected $tokens;

	/**
	 * Prefix object instance
	 * @var instance
	 */
	protected $prefix_object = null;

	/**
	 * Constructor method
	 *
	 * @param \phpbb\db\driver\factory $db DBAL object
	 * @param \phpbb\cache\driver_interface $cache Cache driver object
	 * @param \phpbb\template $template Template object
	 * @param int $id Instance ID
	 */
	public function __construct(\phpbb\db\driver\factory $db, \phpbb\cache\driver\driver_interface $cache, \phpbb\template\template $template, \phpbb\di\service_collection $tokens, $id = 0)
	{
		parent::__construct();

		$this['id'] = $id;
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;
		$this->tokens = $tokens;

		if ($this['id'])
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
	 * @param   bool    $return_parsed If true, return is the parsed prefix
	 *								otherwise, it is the plaintext version
	 * @return	 string				Plaintext prefix
	 */
	public function parse($block = '', $return_parsed = false)
	{
		if (!$this->prefix_object instanceof \imkingdavid\prefixed\core\prefix)
		{
			$this->setPrefixObject(new \imkingdavid\prefixed\core\prefix($this->db, $this->cache, $this->template, $this['prefix']));
		}

		if (!$this['prefix_object']->loaded() && !$this['prefix_object']->load())
		{
			return '';
		}

		foreach (json_decode($this['token_data'], true) as $data)
		{
			// If a token was used and is no longer available
			// we skip parsing it. It will show up ugly in the prefix
			// but that's not my fault.
			if (!isset($this->tokens[$data['service']]))
			{
				continue;
			}

			$this['prefix_object']['title'] = $this->tokens[$data['service']]->apply_token_data($this['prefix_object']['title'], $data['data']);
		}

		// To clarify, the second argument here is simply replacing the prefix
		// ID with the instance ID in the template, and adding the prefix ID
		// as its own PREFIX variable
		return $this['prefix_object']->parse($block, [
			'ID' => $this['id'],
			'PREFIX' => $this['prefix_object']['id'],
			'ORDER' => $this['ordered'],
		], '', $return_parsed);
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

	/**
	 * Set the prefix ID for this instance
	 *
	 * @param int $id
	 */
	public function setId($id) {
		$this['id'] = (int) $id;
	}

	/**
	 * Set the prefix object for this instance
	 *
	 * @param prefix $prefix
	 * @return null
	 */
	public function setPrefix($prefix) {
		$this['prefix'] = (int) $prefix;
	}

	/**
	 * Set the prefix object for this instance
	 *
	 * @param \imkingdavid\prefixed\core\prefix $prefix
	 * @return null
	 */
	public function setPrefixObject(\imkingdavid\prefixed\core\prefix $prefix)
	{
		$this['prefix_object'] = $prefix;
	}

	/**
	 * Set the prefix topic ID for this instance
	 *
	 * @param int $topic
	 * @return null
	 */
	public function setTopic($topic) {
		$this['topic'] = (int) $topic;
	}

	/**
	 * Set the prefix object for this instance
	 *
	 * @param prefix $prefix
	 * @return null
	 */
	public function setTokenData($token_data) {
		$this['token_data'] = $token_data;
	}

	/**
	 * Set the prefix object for this instance
	 *
	 * @param prefix $prefix
	 * @return null
	 */
	public function setOrdered($ordered) {
		$this['ordered'] = $ordered;
	}

	/**
	 * Get the instance ID
	 *
	 * @return int
	 */
	public function getId() {
		return isset($this['id']) ? $this['id'] : 0;
	}

	/**
	 * Get the prefix ID
	 *
	 * @return int
	 */
	public function getPrefix() {
		return isset($this['prefix']) ? $this['prefix'] : 0;
	}

	/**
	 * Get the prefix object
	 *
	 * @return \imkingdavid\prefixed\core\prefix
	 */
	public function getPrefixObject() {
		return isset($this['prefix_object']) ? $this['prefix_object'] : null;
	}

	/**
	 * Get the topic ID
	 *
	 * @return int
	 */
	public function getTopic() {
		return isset($this['topic']) ? $this['topic'] : 0;
	}

	/**
	 * Get the token data
	 *
	 * @return string
	 */
	public function getTokenData() {
		return isset($this['token_data']) ? $this['token_data'] : '';
	}

	/**
	 * Get the order
	 *
	 * @return int
	 */
	public function getOrdered() {
		return isset($this['ordered']) ? $this['ordered'] : 0;
	}
}
