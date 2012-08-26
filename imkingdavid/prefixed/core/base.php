<?php

class phpbb_ext_imkingdavid_prefixed_core_base
{
	/**
	 * Database object instance
	 * @var dbal
	 */
	protected $db;

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
	private $request;

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
	public function load_prefixes($refresh = false)
	{
		if (!empty($this->prefixes) && !$refresh)
		{
			return $this->prefixes;
		}

		if (($this->prefixes = $this->cache->get('_prefixes')) === false || $refresh)
		{
			$sql = 'SELECT id, title, short, style, users, forums
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
	public function load_prefix_instances($refresh = false)
	{
		if (!empty($this->prefix_instances) && !$refesh)
		{
			return $this->prefix_instances;
		}

		if (($this->prefix_instances = $this->cache->get('_prefixes_used')) === false || $refresh)
		{
			$sql = 'SELECT id, prefix, topic, ordered, token_data
				FROM ' . PREFIX_INSTANCES_TABLE;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->prefix_instances[$row['id']] = array(
					'id'			=> $row['id'],
					'prefix'		=> $row['prefix'],
					'topic'			=> $row['topic'],
					'ordered'		=> $row['ordered'],
					'token_data'	=> $row['token_data'],
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
	 * @param	int		$topic_id	ID of the topic
	 * @param	string	$block		Name of the block to send to the 
	 * @return	string	Prefixes all in one string
	 */
	public function load_prefixes_topic($topic_id, $block = '')
	{
		if (!$this->load_prefix_instances())
		{
			return '';
		}

		$topic_prefixes = array();
		foreach ($this->prefix_instances as $instance)
		{
			if ($instance['topic'] == $topic_id)
			{
				$topic_prefixes[] = new phpbb_ext_imkingdavid_prefixed_core_instance($this->db, $this->cache, $this->template, $instance['id']);
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
			$return_string .= $prefix->parse($block, true);
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
		return $a->get('ordered') == $b->get('ordered') ? 0 : ($a->get('ordered') > $b->get('ordered') ? 1 : -1);
	}

	/**
	 * Add a prefix to a topic
	 *
	 * @param int $topic_id Topic ID
	 * @param int $prefix_id Prefix ID
	 * @param int $forum_id Forum ID
	 * @return null
	 */
	public function add_topic_prefix($topic_id, $prefix_id, $forum_id)
	{
		$allowed_prefixes = $this->get_allowed_prefixes($this->user->data['user_id'], $forum_id);

		if (count($allowed_prefixes) === count($this->load_prefixes_topic()) || !in_array($prefix_id, $allowed_prefixes) || !in_array($prefix_id, array_keys($this->prefixes)))
		{
			return;
		}

		$prefix_data = $this->prefixes[$prefix_id];
		$prefix_title = $prefix_data['title'];

		$token_data = array();

		/**
		 * This is where tokens get applied to a prefix
		 * Other extensions can add tokens using this, or can otherwise modify
		 * the prefix title as they see fit
		 *
		 * @event prefixed.modify_prefix_title
		 * @var	string	prefix_title	Title used to check for tokens
		 *								It is possible to modify the title
		 *								but that is not recommended.
		 * @var	array	token_data		Array of tokens and data:
		 *								'TOKEN'	=> 'value'
		 * @since 1.0.0-A1
		 */
		$vars = array('token_data', 'prefix_title');
		extract($phpbb_dispatcher->trigger_event('prefixed.modify_prefix_title', compact($vars)));

		$token_data = serialize($token_data);

		$sql_ary = array(
			'prefix'		=> $title,
			'topic'			=> $topic_id,
			'ordered'		=> $this->count_topic_prefixes($topic_id) + 1,
			'token_data'	=> $row['token_data'],
		);

		$sql = 'INSERT INTO ' . PREFIX_INSTANCES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$this->load_prefix_instances(true);
	}

	/**
	 * Remove a single prefix from a topic
	 *
	 * @param int	$topic_id	Topic ID
	 * @param mixed	$prefix_id	Array or Integer Prefix ID
	 *							If empty, all prefixes on that topic are
	 *							removed.
	 * @return null
	 */
	public function remove_topic_prefixes($topic_id, $prefix_id)
	{
		if (!empty($prefix_id) && !is_array($prefix_id))
		{
			$prefix_id = array($prefix_id);
		}
		$sql_and = !empty($prefix_id) ? 'AND ' . $this->db->sql_in_set('prefix_id', $prefix_id) : '';

		$sql = 'DELETE FROM ' . PREFIX_INSTANCES_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . "
				$sql_and";
		$this->db->sql_query($sql);

		$this->load_prefix_instances(true);
	}

	/**
	 * Obtain the prefixes allowed to be used by this user in this forum
	 *
	 * @param int $user_id The user to check
	 * @param int $forum_id The forum to check
	 * @return array Allowed prefix IDs
	 */
	public function get_allowed_prefixes($user_id, $forum_id = 0)
	{
		if (empty($this->prefixes))
		{
			return array();
		}

		if (!function_exists('group_memberships'))
		{
			include("{$phpbb_root_path}includes/functions_user.$phpEx");
		}
		$groups = group_memberships(false, $user_id);

		$prefixes = $this->prefixes;
		$allowed_prefixes = array();

		foreach ($prefixes as $prefix)
		{
			// If we are given a forum ID to filter by, only allow use of the
			// prefix if it is allowed in this forum
			if ($forum_id && !in_array($forum_id, explode(',', $prefix['forums'])))
			{
				continue;
			}

			// If any groups the user is a part of match any allowed groups,
			// we allow use of the prefix
			foreach ($groups as $group)
			{
				if (in_array($group['group_id'], explode(',', $prefix['groups'])))
				{
					$allowed_prefixes[] = $prefix['id'];
					continue 2;
				}
			}

			// Lastly, if we are not in allowed group, check allowed users
			if (in_array($user_id, explode(',', $prefix['users'])))
			{
				$allowed_prefixes[] = $prefix['id'];
				continue;
			}
		}

		return $allowed_prefixes;
	}

	/**
	 * Generate template for the posting form
	 *
	 * @var int		$forum_id		ID of the forum
	 * @var int		$topic_id		ID of the topic
	 * @return null
	 */
	public function generate_posting_form($forum_id, $topic_id = 0)
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
		$this->load_prefixes();

		foreach ($this->prefixes as $prefix)
		{
			if (in_array($prefix['id'], $topic_prefixes_used))
			{
				continue;
			}

			$this->template->assign_block_vars('prefix_option', array(
				'ID'		=> $prefix['id'],
				'TITLE'		=> $prefix['title'],
				'STYLE'		=> $prefix['style'],
				'USERS'		=> $prefix['users'],
				'FORUMS'	=> $prefix['forums'],
			));
		}
	}

	/**
	 * Get the number of prefixes in the topic
	 *
	 * @param int $topic_id Topic ID
	 * @return int Number of prefixes in the topic
	 */
	public function count_topic_prefixes($topic_id)
	{
		$count = 0;

		foreach ($this->prefix_instances as $instance)
		{
			if ($instance['topic'] == $topic_id)
			{
				$count++;
			}
		}

		return $count;
	}
}
