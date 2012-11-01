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

class phpbb_ext_imkingdavid_prefixed_core_manager
{
	/**
	 * Database object instance
	 * @var dbal
	 */
	protected $db;

	/**
	 * Cache object instance
	 * @var phpbb_cache_drive_interface
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
	public function __construct(dbal $db, phpbb_cache_driver_interface $cache, phpbb_template $template, phpbb_request $request)
	{
		global $phpbb_root_path, $phpEx;
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;
		$this->request = $request;

		$this->load_prefixes()
			->load_prefix_instances();
	}

	/**
	 * Load all prefixes
	 * NOTE: This sets the prefixes property and returns the current
	 * instance of this object. Use the get_prefixes to get the array
	 * returned.
	 *
	 * @return $this
	 */
	public function load_prefixes($refresh = false)
	{
		if (!$refresh && !empty($this->prefixes))
		{
			return $this;
		}

		if (($this->prefixes = $this->cache->get('_prefixes')) === false || $refresh)
		{
			$sql = 'SELECT id, title, short, style, users, forums
				FROM ' . PREFIXES_TABLE;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->prefixes[$row['id']] = [
					'id'			=> $row['id'],
					'title'			=> $row['title'],
					'short'			=> $row['short'],
					'style'			=> $row['style'],
					'users'			=> $row['users'],
					'forums'		=> $row['forums'],
				];
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_prefixes', $this->prefixes);
		}

		return $this;
	}

	/**
	 * Get the array of prefixes
	 *
	 * @param bool $refresh Whether to use the cached version or load from the
	 *	database.
	 * @return array Prefix data
	 */
	public function get_prefixes($refresh = false)
	{
		$this->load_prefixes();

		return $this->prefixes;
	}

	/**
	 * Load all prefix instances
	 * NOTE: This sets the prefix_instances property and returns the current
	 * instance of this object. Use get_prefix_instance() to get the array
	 * returned.
	 *
	 * @return $this
	 */
	public function load_prefix_instances($refresh = false)
	{
		if (!$refresh && !empty($this->prefix_instances))
		{
			return $this;
		}

		if (($this->prefix_instances = $this->cache->get('_prefixes_used')) === false || $refresh)
		{
			$sql = 'SELECT id, prefix, topic, ordered, token_data
				FROM ' . PREFIX_INSTANCES_TABLE;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->prefix_instances[$row['id']] = [
					'id'			=> $row['id'],
					'prefix'		=> $row['prefix'],
					'topic'			=> $row['topic'],
					'ordered'		=> $row['ordered'],
					'token_data'	=> $row['token_data'],
				];
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('_prefixes_used', $this->prefix_instances);
		}

		return $this;
	}

	/**
	 * Get the array of prefix instances
	 *
	 * @param bool $refresh Whether to use the cached version or load from the
	 *	database.
	 * @return array Prefix data
	 */
	public function get_prefix_instances($refresh = false)
	{
		$this->load_prefix_instaces();

		return $this->prefix_instances;
	}

	/**
	 * Load a topic's prefix instances
	 *
	 * @param	int		$topic_id	ID of the topic
	 * @param	string	$block		Name of the block to send to the template
	 * @return	string	Plaintext string of a topic's prefixes
	 */
	public function load_prefixes_topic($topic_id, $block = '')
	{
		if (!$this->count_prefix_instances())
		{
			return '';
		}

		$topic_prefixes = [];
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
		usort($topic_prefixes, [$this, 'sort_topic_prefixes']);

		$return_string = '';
		foreach ($topic_prefixes as $prefix)
		{
			$return_string .= $prefix->parse($block);
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

		$token_data = [];

		/**
		 * This is where tokens get applied to a prefix
		 * Other extensions can add tokens using this, or can otherwise modify
		 * the prefix title as they see fit.
		 *
		 * NOTE: See the get_token_data method in the prefixed_core_listener for
		 * example syntax and usage.
		 *
		 * @event prefixed.modify_prefix_title
		 * @var	string	prefix_title	Title used to check for tokens
		 *								It is possible to modify the title
		 *								but that is not recommended.
		 * @var	array	token_data		Array of tokens and data:
		 *								'TOKEN'	=> 'value'
		 * @since 1.0.0-A1
		 */
		$vars = ['token_data', 'prefix_title'];
		extract($phpbb_dispatcher->trigger_event('prefixed.modify_prefix_title', compact($vars)));

		$token_data = serialize($token_data);

		$sql_ary = [
			'prefix'		=> $title,
			'topic'			=> $topic_id,
			'ordered'		=> $this->count_topic_prefixes($topic_id) + 1,
			'token_data'	=> $token_data,
		];

		$sql = 'INSERT INTO ' . PREFIX_INSTANCES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$this->load_prefix_instances(true);
	}

	/**
	 * Remove the specified or all prefixes from a topic
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
			$prefix_id = [$prefix_id];
		}

		$sql_and = !empty($prefix_id) ? ' AND ' . $this->db->sql_in_set('prefix_id', $prefix_id) : '';
		$sql = 'DELETE FROM ' . PREFIX_INSTANCES_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . "$sql_and";
		$this->db->sql_query($sql);

		$this->load_prefix_instances(true);
	}

	/**
	 * Obtain the prefixes allowed to be used by this user in this forum
	 *
	 * @param int $user_id The user to check
	 * @param int $forum_id The forum to check
	 *						If this is value evaluates to false, not forum ID
	 *						restriction is placed on prefixes
	 * @return array Allowed prefix IDs
	 */
	public function get_allowed_prefixes($user_id, $forum_id = 0)
	{
		if (empty($this->prefixes))
		{
			return [];
		}

		$groups = $allowed_prefixes = [];

		if (!function_exists('group_memberships'))
		{
			include("{$phpbb_root_path}includes/functions_user.$phpEx");
		}

		foreach (group_memberships(false, $user_id) as $membership)
		{
			$groups[] = $membership['group_id'];
		}

		foreach ($this->prefixes as $prefix)
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
				if (in_array($group, explode(',', $prefix['groups'])))
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
	public function generate_posting_form($post_id = 0)
	{
		if (!$post_id)
		{
			return;
		}

		// Get some information from the database
		$sql = 'SELECT t.topic_first_post_id, t.forum_id, t.topic_id
			FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p 
			WHERE p.post_id = ' . (int) $post_id . '
				AND t.topic_id = p.topic_id';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Only edit the first post of the topic
		if ((int) $row['topic_first_post_id'] !==  (int) $post_id)
		{
			return;
		}
		$topic_id = $row['topic_id'];
		$forum_id = $row['forum_id'];

		$topic_prefixes_used = [];
		foreach ($this->prefix_instances as $instance)
		{
			if ((int) $instance['topic'] === (int) $topic_id)
			{
				$topic_prefixes_used[] = $instance['prefix'];
			}
		}

		foreach ($this->prefixes as $prefix)
		{
			if (in_array($prefix['id'], $topic_prefixes_used))
			{
				foreach ($this->prefix_instances as $instance_ary)
				{
					if ($prefix['id'] == $instance_ary['prefix'])
					{
						$instance = new phpbb_ext_imkingdavid_prefixed_core_instance($this->db, $this->cache, $this->template, $instance['id']);
						$instance->parse('prefix_used');
					}
				}
			}
			else
			{
				$style = '';
				$style_ary = json_decode($prefix['style']);
				foreach ($style_ary as $element => $value)
				{
					$style .= $element . ': ' . $value . ';';
				}
				$this->template->assign_block_vars('prefix_option', [
					'ID'		=> $prefix['id'],
					'SHORT'		=> $prefix['short'],
					'TITLE'		=> $prefix['title'],
					'STYLE'		=> $style,
				]);
			}
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

		array_map(function($instance) use(&$count, $topic_id) {
			if ($instance['topic'] == $topic_id)
			{
				$count++;
			}
		}, $this->prefix_instances);

		return $count;
	}

	/**
	 * Get the number of prefixes in total
	 *
	 * @return int
	 */
	public function count_prefixes()
	{
		return sizeof($this->prefixes);
	}

	/**
	 * Get the number of prefix instances in total
	 *
	 * @return int
	 */
	public function count_prefix_instances()
	{
		return sizeof($this->prefix_instances);
	}
}