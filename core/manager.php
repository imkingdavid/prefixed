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
	 * @var \phpbb\db\driver\factory
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
	 * User object
	 * @var \phpbb\user
	 */
	protected $user;

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
	 * @param \phpbb\db\driver\factory $db Database object
	 * @param \phpbb\cache\driver\driver_interface $cache Cache object
	 * @param \phpbb\template\template $template Template object
	 * @param \phpbb\request\request_interface $request Request object
	 * @param \phpbb\di\service_collection $tokens
	 * @param \phpbb\controller\helper $controller_helper
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\db\driver\factory $db, \phpbb\cache\driver\driver_interface $cache, \phpbb\template\template $template, \phpbb\request\request_interface $request, \phpbb\user $user, \phpbb\di\service_collection $tokens, \phpbb\controller\helper $helper, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;
		$this->request = $request;
		$this->user = $user;
		$this->tokens = $tokens;
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

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
		if (false === ($this->prefixes = $this->cache->get('_prefixes')) || $refresh)
		{
			// If we're refreshing, we have to reset the array first
			$this->prefixes = [];
			$sql = 'SELECT id, title, short, users, forums, groups, bbcode_uid, bbcode_bitfield
				FROM ' . PREFIXES_TABLE;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->prefixes[$row['id']] = [
					'id'				=> $row['id'],
					'title'				=> $row['title'],
					'short'				=> $row['short'],
					'users'				=> $row['users'],
					'forums'			=> $row['forums'],
					'groups'			=> $row['groups'],
					'bbcode_uid'		=> $row['bbcode_uid'],
					'bbcode_bitfield'	=> $row['bbcode_bitfield'],
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
		if ($refresh || empty($this->prefixes))
		{
			$this->load_prefixes($refresh);
		}

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
		if (false === ($this->prefix_instances = $this->cache->get('_prefixes_used')) || $refresh)
		{
			// If we're refreshing, we have to reset the array first
			$this->prefix_instances = [];
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
		if ($refresh || empty($this->prefix_instances))
		{
			$this->load_prefix_instaces($refresh);
		}

		return $this->prefix_instances;
	}

	/**
	 * Load a topic's prefix instances
	 *
	 * @param	int		$topic_id	ID of the topic
	 * @param	string	$block		Name of the block to send to the template
	 * @param   bool    $return_parsed If true, return is the parsed prefix
	 *								otherwise, it is the plaintext version
	 * @return	string	Plaintext string of a topic's prefixes
	 */
	public function load_prefixes_topic($topic_id, $block = '', $return_parsed = false)
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

		$prefixes_array = array();
		foreach ($topic_prefixes as $instance)
		{
			$vars = [];
			// A URL to a viewforum view filtered by the prefix
			if ($this->request->variable('f', 0))
			 {
			    $vars['URL'] = append_sid($this->root_path . 'viewforum.php', ['f' => $this->request->variable('f', 0), 'prefix' => $instance['prefix']]);
			 }
			 $prefixes_array[] = $instance->parse($block, $vars, $return_parsed) . '&nbsp;';
		}
		$return_string = implode('&nbsp;', $prefixes_array);

		return $return_string;
	}

	/**
	 * Get the IDs of prefix instances applied to the topic
	 *
	 * @param int $topic_id
	 * @return array
	 */
	public function load_instance_ids_topic($topic_id)
	{
		if (!$this->count_prefix_instances())
		{
			return [];
		}

		$topic_prefixes = [];
		if (!empty($this->prefix_instances))
		{
			foreach ($this->prefix_instances as $instance)
			{
				if ((int) $instance['topic'] === (int) $topic_id)
				{
					$topic_prefixes[] = $instance['id'];
				}
			}
		}

		return $topic_prefixes;
	}

	/**
	 * Get the IDs of the prefixes that were submitted with a topic posted/edited/previewed
	 *
	 * @return array Array of prefix IDs
	 */
	public function get_submitted_prefixes()
	{
		// Due to .sortable('serialize') $ids will be a string like: 'prefix[]=4'
		// I need the number. That's in index two of $prefix_ids
		$used_ids = $this->request->variable('prefixes_used', '');
		if ($used_ids && preg_match_all('/(prefix\[\]=(\d+)&?)+/', $used_ids, $prefix_ids) && isset($prefix_ids[2]))
		{
			$used_ids = $prefix_ids[2];
		}

		return is_array($used_ids) ? $used_ids : [];
	}

	/**
	 * Custom sort function used by usort() to order a topic's prefixes by their "ordered" property
	 *
	 * @param instance|array $a First comparison argument
	 * @param instance|array $b Second comparison argument
	 * @return int 0 for equal, 1 for a greater than b, -1 for b greater than a
	 */
	static public function sort_topic_prefixes($a, $b)
	{
		if ((!is_array($a) && !($a instanceof instance)) || (!is_array($b) && !($b instanceof instance)))
		{
			return 0;
		}

		$a_ordered = (int) is_array($a) ? $a['ordered'] : $a->getOrdered();
		$b_ordered = (int) is_array($b) ? $b['ordered'] : $b->getOrdered();
		return $a_ordered === $b_ordered ? 0 : ($a_ordered > $b_ordered ? 1 : -1);
	}

	/**
	 * Add a prefix to a topic
	 *
	 * @param int $topic_id Topic ID
	 * @param int $prefix_id Prefix ID
	 * @param int $forum_id Forum ID
	 * @param int $order The order of the prefix
	 * @return null
	 */
	public function add_topic_prefix($topic_id, $prefix_id, $forum_id, $order = 0)
	{
		$allowed_prefixes = $this->get_allowed_prefixes($this->user->data['user_id'], $forum_id);

		if (count($allowed_prefixes) === count($this->load_instance_ids_topic($topic_id)) || !in_array($prefix_id, $allowed_prefixes) || !in_array($prefix_id, array_keys($this->prefixes)))
		{
			return;
		}

		$prefix_data = $this->prefixes[$prefix_id];
		$prefix_title = $prefix_data['title'];

		$sql_ary = [
			'prefix'		=> $prefix_id,
			'topic'			=> $topic_id,
			'ordered'		=> $order ?: $this->count_topic_prefixes($topic_id) + 1,
			'token_data'	=> $this->calculateTokenDataForInstance($prefix_title, $topic_id, $prefix_id, $forum_id),
		];

		$sql = 'INSERT INTO ' . PREFIX_INSTANCES_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$this->load_prefix_instances(true);
	}

	/**
	 * Go through each available token and determine the token data that should
	 * be stored for the given prefix info
	 *
	 * @param string $prefix_title
	 * @param int $topic_id
	 * @param int $prefix_id
	 * @param int $forum_id
	 */
	public function calculateTokenDataForInstance($prefix_title, $topic_id, $prefix_id, $forum_id)
	{
		$token_data = [];

		// Here is where we go through all of the tokens
		foreach ($this->tokens as $token_object) {
			if (!($token_object instanceof \imkingdavid\prefixed\core\token\token_interface)) {
				throw new \imkingdavid\prefixed\core\token\exception($this->user->lang('Token objects must implement \imkingdavid\prefixed\core\token\token_interface'));
			}
			// The assignment operator here is intentional
			if ($data = $token_object->get_token_data($prefix_title, $topic_id, $prefix_id, $forum_id)) {
				$token_data[] = $data;
			}
		}

		return json_encode($token_data);
	}

	/**
	 * Add, retain, or remove prefixes on a given topic based on the list
	 * of currently applied prefixes for that topic, compared to the list
	 * of prefixes selected on the posting form.
	 *
	 * @param int $topic_id
	 * @param array $prefix_ids
	 * @param int $forum_id
	 * @return null
	 */
	public function set_topic_prefixes($topic_id, $prefix_ids, $forum_id)
	{
		$this->load_prefix_instances();
		$all_used_prefixes = $this->prefix_instances;
		$this_topics_prefixes = [];
		if (!empty($all_used_prefixes)) {
			foreach($all_used_prefixes as $prefix)
			{
				if ($prefix['topic'] == $topic_id)
				{
					$this_topics_prefixes[] = $prefix['prefix'];
				}
			}
		}

		// If the topic doesn't have prefixes and we aren't adding any
		// there's nothing to do here
		if (empty($prefix_ids) && empty($this_topics_prefixes))
		{
			return;
		}
		// If the topic does have prefixes and our prefix array is empty
		// we are removing all of them
		else if (empty($prefix_ids) && !empty($this_topics_prefixes))
		{
			// 0 for the second parameter means ALL
			// Otherwise, you can give an array or a single prefix ID
			// to remove.
			// NOTE this is the prefix ID, not the instance ID
			$this->remove_topic_prefixes($topic_id, 0);
		}
		// If the topic does not have any prefixes and our prefix array is
		// not empty, we're adding all of them
		else if (!empty($prefix_ids) && empty($this_topics_prefixes))
		{
			foreach ($prefix_ids as $prefix_id)
			{
				$this->add_topic_prefix($topic_id, $prefix_id, $forum_id);
			}
		}
		// Otherwise, we have some more difficult logic because we need to
		// determine which are being added, removed, and retained
		else
		{
			// We're going to be removing $retained_ids from $prefix_ids
			// But we need to $prefix_ids array later for ordering
			$original_ids = $prefix_ids;

			// First we figure out all of the IDs that are present in both
			// arrays. These we discard because they are being retained
			// (that sentence sounds odd)
			$retained_ids = array_intersect($prefix_ids, $this_topics_prefixes);

			// Now discard those
			if (!empty($retained_ids)) {
				foreach ($retained_ids as $retained_id) {
					$remove_id = array_search($retained_id, $prefix_ids);
					if (false !== $remove_id) {
						unset($prefix_ids[$remove_id]);
						$prefix_ids = array_values($prefix_ids);
					}
					$remove_id = null;

					$remove_id = array_search($retained_id, $this_topics_prefixes);
					if (false !== $remove_id) {
						unset($this_topics_prefixes[$remove_id]);
						$this_topics_prefixes = array_values($this_topics_prefixes);
					}

					// Set the new order for the prefix instance
					$sql = 'UPDATE '. PREFIX_INSTANCES_TABLE . '
						SET ordered = ' . (array_search($retained_id, $original_ids) + 1) . '
						WHERE prefix = ' . (int) $retained_id;
					$this->db->sql_query($sql);
				}
				$this->load_prefix_instances(true);
			}

			if (!empty($prefix_ids))
			{
				// We've removed the prefixes to be retained now, so the ones
				// that are left in $prefix_ids need to be added
				foreach ($prefix_ids as $prefix_id)
				{
					$this->add_topic_prefix($topic_id, $prefix_id, $forum_id, array_search($prefix_id, $original_ids) + 1);
				}
			}

			if (!empty($this_topics_prefixes))
			{
				// And the ones that are left in $this_topics_prefixes need to be
				// removed
				$this->remove_topic_prefixes($topic_id, $this_topics_prefixes);
			}
		}
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
			$prefix_id = [(int) $prefix_id];
		}

		$sql_and = !empty($prefix_id) ? ' AND ' . $this->db->sql_in_set('prefix', $prefix_id) : '';
		$sql = 'DELETE FROM ' . PREFIX_INSTANCES_TABLE . '
			WHERE topic = ' . (int) $topic_id . "$sql_and";
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
			include("{$this->root_path}includes/functions_user.{$this->php_ext}");
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
	 * @var array	$event	Event array directly from the listener
	 * @return null
	 */
	public function generate_posting_form($event)
	{
		// First, let's see what we can get from the URL
		$post_id = $this->request->variable('p', 0);
		$topic_id = $this->request->variable('t', 0);
		$forum_id = $this->request->variable('f', 0);

		// And we can define a few variables we'll be using from the event data
		// Since we're not changing anything, we can just assign by value, not reference
		$mode = $event['mode'];
		$post_data = $event['post_data'];

		// We only show the prefix manager thing during new topic creation
		// and when we're editing the first post of a topic
		// So if we're not 'post'-ing or 'edit'-ing, gtfo!
		if (!in_array($mode, ['post', 'edit']))
		{
			return;
		}

		if ('edit' === $mode && (int) $post_id !== (int) $post_data['topic_first_post_id'])
		{
			return;
		}

		// If we're editing, we may not have a topic_id or forum_id
		// from the URL so we can grab that from the $post_data array
		// that we got from the event listener. Thank you event listener! <3
		if ('edit' === $mode) {
			$topic_id = $post_data['topic_id'];
			$forum_id = $post_data['forum_id'];
		}

		// If there are no allowed prefixes for the current user in the current forum, let's stop wasting time.
		if ((!$this->prefixes && !$this->prefix_instances) || !($allowed_prefixes = $this->get_allowed_prefixes($this->user->data['user_id'], $forum_id)))
		{
			return;
		}

		$topic_prefixes_used = [];

		// code borrowed from listener.php's manage_prefixes_on_posting()
		// so that we can handle prefix changes during Preview
		$submitted_prefixes = $this->get_submitted_prefixes();

		if ($submitted_prefixes)
		{
			foreach ($submitted_prefixes as $ordered => $prefix_id)
			{
				$prefix = $this->get_prefix($prefix_id);
				$instance_object = new \imkingdavid\prefixed\core\instance($this->db, $this->cache, $this->template, $this->tokens);
				$instance_object->setTokenData($this->calculateTokenDataForInstance($prefix['title'], $topic_id, $prefix_id, $forum_id));
				$instance_object->setTopic($topic_id);
				$instance_object->setOrdered($ordered);
				$instance_object->setPrefix($prefix_id);
				$instance_object->setPrefixObject($prefix);
				$topic_prefixes_used[$prefix_id] = $instance_object;
			}
		}
		else
		{
			// We want to sort the prefixes by the 'ordered' property, and we can do that with our custom sort function
			usort($this->prefix_instances, [$this, 'sort_topic_prefixes']);
			foreach ($this->prefix_instances as $instance)
			{
				if (0 !== $topic_id && (int) $instance['topic'] === (int) $topic_id)
				{
					$topic_prefixes_used[$instance['prefix']] = $instance;
				}
			}
		}

		// We have to go by instance instead of prefix so we are going in
		// the right order
		foreach ($topic_prefixes_used as $instance_ary)
		{
			$current_prefix = null;
			foreach ($this->prefixes as $prefix)
			{
				if ((int) $prefix['id'] === (int) $instance_ary['prefix'])
				{
					$current_prefix = $prefix;
					break;
				}
			}

			if ($current_prefix !== null && in_array($current_prefix['id'], $allowed_prefixes) && isset($topic_prefixes_used[$current_prefix['id']]))
			{
				if (!($instance_ary instanceof \imkingdavid\prefixed\core\instance)) {
					$instance_ary = $this->get_instance($instance_ary['id']);
				}
				$instance_ary->parse('prefix_used');
			}
		}
		// Now we get all prefixes that are allowed but haven't been used
		foreach ($this->prefixes as $prefix)
		{
			if (in_array($prefix['id'], $allowed_prefixes) && !isset($topic_prefixes_used[$prefix['id']]))
			{
				$this->get_prefix($prefix['id'])->parse('prefix_option');
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

		if (false !== $this->prefix_instances) {
			array_map(function($instance) use (&$count, $topic_id) {
				if ((int) $instance['topic'] === (int) $topic_id)
				{
					$count++;
				}
			}, $this->prefix_instances);
		}

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
		return new instance($this->db, $this->cache, $this->template, $this->tokens, $instance_id);
	}

	/**
	 * Create a prefix object
	 *
	 * @param int $prefix_id
	 * @return imkingdavid\prefixed\core\prefix
	 */
	public function get_prefix($prefix_id)
	{
		return new prefix($this->db, $this->cache, $this->template, $prefix_id);
	}
}
