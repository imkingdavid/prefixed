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

class manager
{
	/**
	 * Database object instance
	 * @var \phpbb\db\driver\driver
	 */
	protected $db;

	/**
	 * Cache object instance
	 * @var \phpbb\cache\driver\driver_interface
	 */
	protected $cache;

	/**
	 * Template object
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * Request object
	 * @var \phpbb\request\request
	 */
	protected $request;

	/**
	 * Tokens
	 * @var array
	 */
	protected $tokens;

	/**
	 * Prefix instances
	 * @var array
	 */
	protected $prefix_instances;

	/**
	 * Prefixes
	 * @var array
	 */
	protected $prefixes;

	/**
	 * Constructor method
	 *
	 * @param \phpbb\db\driver\driver $db Database object
	 * @param \phpbb\cache\driver\driver_interface $cache Cache object
	 * @param \phpbb\template\template $template Template object
	 * @param \phpbb\request\request_interface $request Request object
	 * @param array $tokens
	 */
	public function __construct(\phpbb\db\driver\driver $db, \phpbb\cache\driver\driver_interface $cache, \phpbb\template\template $template, \phpbb\request\request_interface $request, $tokens)
	{
		global $phpbb_root_path, $phpEx;
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;
		$this->request = $request;
		$this->finder = $finder;
		$this->tokens = $tokens;

		$this->load_prefixes()
			->load_prefix_instances();
	}

	/**
	 * Load all prefixes
	 *
	 * NOTE: This sets the prefixes property and returns the current
	 * instance of this object. Use the get_prefixes to get the array
	 * returned.
	 *
	 * @param bool $refresh True means load from database instead of cache
	 * @return $this
	 */
	public function load_prefixes($refresh = false)
	{
		if (($this->prefixes = $this->cache->get('_prefixes')) === false || $refresh)
		{
			$sql = 'SELECT id, title, short, users, forums
				FROM ' . PREFIXES_TABLE;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->prefixes[$row['id']] = [
					'id'			=> $row['id'],
					'title'			=> $row['title'],
					'short'			=> $row['short'],
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
		if (!$refresh && !empty($this->prefixes))
		{
			return $this->prefixes;
		}

		$this->load_prefixes($refresh);

		return $this->prefixes;
	}

	/**
	 * Load all prefix instances
	 *
	 * NOTE: This sets the prefix_instances property and returns the current
	 * instance of this object. Use get_prefix_instance() to get the array
	 * returned.
	 *
	 * @param bool $refresh True means load from database instead of cache
	 * @return $this
	 */
	public function load_prefix_instances($refresh = false)
	{
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
		if (!$refresh && !empty($this->prefix_instances))
		{
			return $this->prefix_instances;
		}

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
		// If there aren't any instantiated prefixes, the topic won't have any
		// to load, so let's just stop right here
		if (!$this->count_prefix_instances())
		{
			return '';
		}

		$topic_prefixes = [];
		if (!empty($this->prefix_instances)) {
			foreach ($this->prefix_instances as $instance)
			{
				if ((int) $instance['topic'] === (int) $topic_id)
				{
					$topic_prefixes[] = $this->get_instance($instance['id']);
				}
			}
		}

		// If this topic has no prefixes
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
	 * @param instance $a First comparison argument
	 * @param instance $b Second comparison argument
	 * @return int 0 for equal, 1 for a greater than b, -1 for b greater than a
	 */
	static public function sort_topic_prefixes(instance $a, instance $b)
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

		// Here is where we go through all of the tokens
		foreach ($this->tokens as $token_object) {
			if (!($token_object instanceof \imkingdavid\prefixed\core\token\token_interface)) {
				throw new \imkingdavid\prefixed\core\token\exception($this->user->lang('Token objects must implement \imkingdavid\prefixed\core\token\token_interface'));
			}
			// The assignment operator here is intentional
			// Basically, it says assign the value of the of right half to
			// the variable on the left and then see if that evaluates as
			// true or false
			if ($data = $token_object->get_token_data($prefix_title, $topic_id, $prefix_id, $forum_id)) {
				$token_data[] = $data;
			}
		}

		$token_data = json_encode($token_data);

		$sql_ary = [
			'prefix'		=> $prefix_title,
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
	 * This does not check auth or ask for confirmation.
	 *
	 * @param int	$topic_id	Topic ID
	 * @param mixed	$prefix_id	Array or Integer Prefix ID
	 *							If empty, ALL prefixes on that topic are
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
		if ($this->prefix_instances)
		{
			foreach ($this->prefix_instances as $instance)
			{
				if ((int) $instance['topic'] === (int) $topic_id)
				{
					$topic_prefixes_used[] = $instance['prefix'];
				}
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
						$instance = $this->get_instance($instance['id']);
						$instance->parse('prefix_used');
					}
				}
			}
			else
			{
				$this->template->assign_block_vars('prefix_option', [
					'ID'		=> $prefix['id'],
					'SHORT'		=> $prefix['short'],
					'TITLE'		=> $prefix['title'],
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

		array_map(function($instance) use (&$count, $topic_id) {
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

	/**
	 * Clear the prefix cache (both prefixes and instances)
	 *
	 * @return null
	 */
	public function clear_prefix_cache()
	{
		$this->cache->destroy('_prefixes');
		$this->cache->destroy('_prefixes_used');
	}

	/**
	 * Get a prefix instance object for the given instance ID
	 *
	 * @return instance
	 */
	public function get_instance($instance_id)
	{
		return new instance($this->db, $this->cache, $this->template, $instance_id);
	}
}
