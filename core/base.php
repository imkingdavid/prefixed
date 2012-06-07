<?php

class phpbb_ext_imkingdavid_prefixed_core_base
{
	/**
	 * Prefix ID
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Database object instance
	 * @var dbal
	 */
	protected $db = null;

	/**
	 * Cache object instance
	 * @var phpbb_cache_drive_base
	 */
	private $cache;

	/**
	 * Prefix instances
	 * @var array
	 */
	private $all_used;

	/**
	 * Prefixes
	 * @var array
	 */
	private $all;

	/**
	 * Constructor method
	 *
	 * @param dbal $db Database object
	 * @param phpbb_cache_driver_base $cache Cache object
	 */
	public function __construct(dbal $db, phpbb_cache_service $cache)
	{
		global $phpbb_root_path, $phpEx;
		$this->db = $db;
		$this->cache = $cache;
	}

	/**
	 * Load all prefixes
	 *
	 * @return array Prefixes
	 */
	public function load_all()
	{
		if (!empty($this->all))
		{
			return $this->all;
		}

		if (($this->all = $this->cache->get('_prefixes')) === false)
		{
			$sql = 'SELECT id, title, short, style, users, forums, token_data
				FROM ' . PREFIXES_TABLE;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->all[$row['id']] = array(
					'id'			=> $row['id'],
					'title'			=> $row['title'],
					'short'			=> $row['short'],
					'style'			=> $row['style'],
					'users'			=> $row['users'],
					'forums'		=> $row['forums'],
					'token_data'	=> $row['token_data'],
				);
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_prefixes', $this->all);
		}

		return $this->all;
	}

	/**
	 * Load all prefix instances
	 *
	 * @return array Prefix instances
	 */
	public function load_all_used()
	{
		if (!empty($this->all_used))
		{
			return $this->all_used;
		}

		if (($this->all_used = $this->cache->get('_prefixes_used')) === false)
		{
			$sql = 'SELECT id, prefix, topic, applied_time, applied_user, ordered
				FROM ' . PREFIXES_USED_TABLE;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->all_used[$row['id']] = array(
					'id'			=> $row['id'],
					'prefix'		=> $row['prefix'],
					'topic'			=> $row['topic'],
					'applied_time'	=> $row['applied_time'],
					'applied_user'	=> $row['applied_user'],
					'ordered'		=> $row['ordered'],
				);
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_prefixes_used', $this->all_used);
		}

		return $this->all_used;
	}

	/**
	 * Load a topic's prefix instances
	 *
	 * @param int $topic_id ID of the topic
	 * @return string Parsed (HTML) prefixes
	 */
	public function load_topic_prefixes($topic_id)
	{
		if (!$this->all_used = $this->load_all_used())
		{
			return '';
		}

		$topic_prefixes = array();
		foreach ($this->all_used as $used)
		{
			if ($used['topic'] == (int) $topic_id)
			{
				$instance = new phpbb_ext_imkingdavid_prefixed_core_instance($this->db, $this->cache, $used['id']);
				$topic_prefixes[] = $instance;
			}
		}

		if (empty($topic_prefixes))
		{
			return '';
		}

		// We want to sort the prefixes by the 'ordered' property, and we can do that with our custom sort function
		usort($topic_prefixes, array('phpbb_ext_imkingdavid_prefixed_core_base', 'sort_topic_prefixes'));

		$return_string = '';
		foreach ($topic_prefixes as $prefix)
		{
			$return_string .= $prefix->parse();
		}

		return $return_string;
	}

	/**
	 * Custom sort function used by usort() to order a topic's prefixes by their "ordered" property
	 *
	 * @param phpbb_ext_imkingdavid_prefixed_core_instance $a First comparison argument
	 * @param phpbb_ext_imkingdavid_prefixed_core_instance $b Second comparison argument
	 * @return int 0 for equal, 1 for a greater than b, -1 for b greater than a
	 */
	static public function sort_topic_prefixes(phpbb_ext_imkingdavid_prefixed_core_instance $a, phpbb_ext_imkingdavid_prefixed_core_instance $b)
	{
		$a_order = $a->get('ordered');
		$b_order = $b->get('ordered');

		if ($a_order == $b_order)
		{
			return 0;
		}

		return ($a_order > $b_order) ? 1 : -1;
	}
}
