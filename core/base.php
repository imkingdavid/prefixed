<?php

class phpbb_ext_imkingdavid_prefixed_core_base
{
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
	 * Template object
	 * @var phpbb_template
	 */
	private $template;

	/**
	 * Request object
	 * @var phpbb_request
	 */

	/**
	 * Prefix instances
	 * @var array
	 */
	private $prefix_instances;

	/**
	 * Prefixes
	 * @var array
	 */
	private $prefixes;

	/**
	 * Constructor method
	 *
	 * @param dbal $db Database object
	 * @param phpbb_cache_driver_base $cache Cache object
	 */
	public function __construct(dbal $db, phpbb_cache_service $cache, phpbb_template $template, phpbb_request $request)
	{
		global $phpbb_root_path, $phpEx;
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;
		$this->request = $request;
	}

	/**
	 * Load all prefixes
	 *
	 * @return array Prefixes
	 */
	public function load_all()
	{
		if (!empty($this->prefixes))
		{
			return $this->prefixes;
		}

		if (($this->prefixes = $this->cache->get('_prefixes')) === false)
		{
			$sql = 'SELECT id, title, short, style, users, forums, token_data
				FROM ' . PREFIXES_TABLE;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->prefixes[$row['id']] = array(
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

			$this->cache->put('_prefixes', $this->prefixes);
		}

		return $this->prefixes;
	}

	/**
	 * Load all prefix instances
	 *
	 * @return array Prefix instances
	 */
	public function load_all_used()
	{
		if (!empty($this->prefix_instances))
		{
			return $this->prefix_instances;
		}

		if (($this->prefix_instances = $this->cache->get('_prefixes_used')) === false)
		{
			$sql = 'SELECT id, prefix, topic, applied_time, applied_user, ordered
				FROM ' . PREFIXES_USED_TABLE;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->prefix_instances[$row['id']] = array(
					'id'			=> $row['id'],
					'prefix'		=> $row['prefix'],
					'topic'			=> $row['topic'],
					'applied_time'	=> $row['applied_time'],
					'applied_user'	=> $row['applied_user'],
					'ordered'		=> $row['ordered'],
				);
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_prefixes_used', $this->prefix_instances);
		}

		return $this->prefix_instances;
	}

	/**
	 * Load a topic's prefix instances
	 *
	 * @param int $topic_id ID of the topic
	 * @param bool $html Whether or not to use HTML (i.e. for the page title, we don't want it)
	 * @return string Prefixes all in one string
	 */
	public function load_topic_prefixes($topic_id, $html = true)
	{
		if (!$this->prefix_instances = $this->load_all_used())
		{
			return '';
		}

		$topic_prefixes = array();
		foreach ($this->prefix_instances as $used)
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
			$return_string .= $prefix->parse($html);
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

	/**
	 * Perform prefix-related actions during posting
	 *
	 * @var string	$action			add|remove|remove_all
	 * @var int		$prefix			The prefix ID (if no topic_id) or instance
	 *								ID (if topic_id provided)
	 * @var int		$topic_id		ID of the topic
	 * @return null
	 */
	static public function perform_posting_action($action, $prefix = 0, $topic_id = 0)
	{
		$prefix_cache = $this->request->variable('prefixes_cache', array());

		if ($action == 'add')
		{
			
		}
	}

	/**
	 * Output template for the posting form
	 *
	 * @var int		$forum_id		ID of the forum
	 * @var int		$topic_id		ID of the topic
	 * @return null
	 */
	static public function output_posting_form($forum_id, $topic_id = 0)
	{
		$topic_prefixes_used = array();
		if ($topic_id)
		{
			foreach ($this->prefix_instances as $instance)
			{
				if ($instance['topic_id'] == $topic_id)
				{
					$topic_prefixes_used[] = $instance['prefix'];
				}
			}
		}

		foreach ($this->prefixes as $prefix)
		{
			if (in_array($prefix['id'], $topic_prefixes_used))
			{
				continue;
			}

			$this->template->assign_block_vars('prefix_option', array(
				'ID'		=> $prefix['id'],
				'TITLE'		=> $prefix['title'],
				'SHORT'		=> $prefix['short'],
				'STYLE'		=> $prefix['style'],
				'USERS'		=> $prefix['users'],
				'FORUMS'	=> $prefix['forums'],
			));
		}
	}
}
