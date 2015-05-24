<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2013 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace imkingdavid\prefixed\event;

// use imkingdavid\prefixed\core\manager;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/**
	 * Database object
	 * @var \phpbb\db\driver\factory
	 */
	protected $db;

	/**
	 * Cache driver object
	 * @var \phpbb\cache\driver\interface
	 */
	protected $cache;

	/**
	 * Template object
	 * @var \phpbb\template
	 */
	protected $template;

	/**
	 * Request object
	 * @var \phpbb\request
	 */
	protected $request;

	/**
	 * User object
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * Prefix manager object
	 * @var imkingdavid\prefixed\core\manager
	 */
	protected $manager;

	/**
	 * Table prefix
	 * @var string
	 */
	protected $table_prefix;

	/**
	 * We don't want to run the setup() method twice so we keep track of
	 * whether or not it has been run. This is mainly for the
	 * core.modify_posting_parameters event that is run before core.user_setup
	 * @var bool
	 */
	protected $setup_has_been_run = false;

	/**
	 * Get subscribed events
	 *
	 * @return array
	 * @static
	 */
	static public function getSubscribedEvents()
	{
		return [
			// phpBB Core Events
			'core.user_setup'					=> 'setup',
			//'core.display_forums_modify_template_vars'	=> 'get_forumlist_topic_prefix',
			'core.viewtopic_modify_page_title'	=> 'get_viewtopic_topic_prefix',
			'core.viewforum_modify_topicrow'	=> 'get_topiclist_topic_prefixes',
			'core.posting_modify_submit_post_after'	=> 'manage_prefixes_on_posting',
			'core.posting_modify_template_vars'	=> 'generate_posting_form',
			'core.mcp_view_forum_modify_topicrow' => 'get_topiclist_topic_prefixes',

			// Events added by this extension
			'prefixed.modify_prefix_title'		=> 'get_token_data',
		];
	}

	/**
	 * Set up the environment
	 *
	 * @param Event $event Event object
	 * @return null
	 */
	public function setup($event)
	{
		// Keep this from running twice
		if(true === $this->setup_has_been_run)
		{
			return;
		}

		global $phpbb_container;

		$this->setup_has_been_run = true;

		$this->container = $phpbb_container;

		// Let's get our table constants out of the way
		$table_prefix = $this->container->getParameter('core.table_prefix');
		define('PREFIXES_TABLE', $table_prefix . 'topic_prefixes');
		define('PREFIX_INSTANCES_TABLE', $table_prefix . 'topic_prefix_instances');

		$this->user = $this->container->get('user');
		$this->db = $this->container->get('dbal.conn');
		$this->request = $this->container->get('request');
		$this->manager = $this->container->get('prefixed.manager');
	}

	/**
	 * Get the actual data to store in the DB for given tokens
	 * This handles the tokens available by default in this extension
	 * Other tokens can add their own methods to listen for the
	 * prefixed.modify_prefix_title event
	 *
	 * @param Event $event Event object
	 * @return null
	 */
	public function get_token_data($event)
	{
		$tokens =& $event['token_data'];

		if (false !== strpos($event['title'], '{DATE}'))
		{
			$tokens['DATE'] = $this->container->get('user')->format_date(microtime(true));
		}

		if (false !== strpos($event['title'], '{USERNAME}'))
		{
			$tokens['USERNAME'] = $this->container->get('user')->data['username'];
		}
	}

	/**
	 * Get the form things for the posting form
	 *
	 * @return Event $event Event object
	 */
	public function generate_posting_form($event)
	{
		$this->user->add_lang_ext('imkingdavid/prefixed', 'prefixed');
		$this->manager->generate_posting_form($event);
	}

	/**
	 * Perform given actions with given prefix IDs on the posting screen
	 *
	 * @param Event $event Event object
	 * @return null
	 */
	public function manage_prefixes_on_posting($event)
	{
		// This event is only called when a post has been submitted.

		// We only want to do things when we're editing the first post
		// or posting a new topic, so those are the only cases in which
		// this function can continue.
		if ('edit' === $event['mode'])
		{
			$sql = 'SELECT topic_first_post_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . (int) $event['topic_id'];
			$result = $this->db->sql_query($sql);
			$first_post_id = $this->db->sql_fetchfield('topic_first_post_id');
			$this->db->sql_freeresult($result);

			if ((int) $event['post_id'] !== (int) $first_post_id)
			{
				return;
			}
		} elseif ('post' !== $event['mode']) {
			return;
		}

		// Due to .sortable('serialize') $ids will be a string like: 'prefix[]=4'
		// I need the number. That's in index two of $prefix_ids
		$used_ids = $this->request->variable('prefixes_used', '') ?: [];
		if ($used_ids && preg_match_all('/(prefix\[\]=(\d)+&?)+/', $used_ids, $prefix_ids) && isset($prefix_ids[2]))
		{
			$used_ids = $prefix_ids[2];
		}

		$this->manager->set_topic_prefixes((int) $event['topic_id'], $used_ids, (int) $event['forum_id']);
	}

	/**
	 * Get the parsed prefix for the current topic, output it to the template
	 * Also gets a plaintext version for the browser page title
	 *
	 * @param Event $event Event object
	 * @return null
	 */
	public function get_viewtopic_topic_prefix($event)
	{
		$event['page_title'] = $this->load_prefixes_topic($event, 'topic_data') . $event['page_title'];
	}

	/**
	 * Get the parsed prefix for each of the topics in the forum row
	 *
	 * @param Event $event Event object
	 * @return null
	 */
	public function get_topiclist_topic_prefixes($event)
	{
		$topic_row = $event['topic_row'];
		$topic_row['TOPIC_PREFIX'] = $this->load_prefixes_topic($event, 'row', '', true);
		$event['topic_row'] = $topic_row;
	}

	/**
	 * Get the parsed prefix for each of the last posts
	 *
	 * @param Event $event Event object
	 * @return null
	 */
	public function get_forumlist_topic_prefix($event)
	{
		$forum_row = $event['forum_row'];
		$forum_row['TOPIC_PREFIX'] = $this->load_prefixes_topic($event, 'row', '', true);
		$event['forum_row'] = $forum_row;
	}

	/**
	 * Helper method that gets the topic prefixes for view(forum/topic) page
	 *
	 * @param Event $event Event object
	 * @param string $array_name Name of the array that contains the topic_id
	 * @param string $block The name of the template block
	 * @return string Plaintext string of topic prefixes
	 */
	protected function load_prefixes_topic($event, $array_name = 'row', $block = 'prefix', $return_parsed = false)
	{
		if (isset($event[$array_name]['topic_id']))
		{
			$topic_id = (int) $event[$array_name]['topic_id'];
		}
		// The following is for if I decide to put the prefix on the last post topic title on forumlist
		// Right now I'm not because I don't want to mess with it
		// else if (isset($event[$array_name]['forum_last_post_id']))
		// {
		// 	// Get the topic ID
		// 	// This results in a looped query, one per forum.
		// 	// As unfortunate as it is, I'm not aware of a way around it
		// 	// besides adding a forum_last_post_topic_id field in the database

		// 	// Ultimately we only want to display the prefix on the topic title
		// 	// Because the last post on the index can be different than the
		// 	// topic title, we don't want to show it if that is the case
		// 	$sql = 'SELECT topic_id
		// 		FROM ' . TOPICS_TABLE . '
		// 		WHERE topic_first_post_id = ' . (int) $event[$array_name]['forum_last_post_id'] . '
		// 			AND topic_last_post_id = ' . (int) $event[$array_name]['forum_last_post_id'];
		// 	$result = $this->db->sql_query($sql);
		// 	$topic_id = (int) $this->db->sql_fetchfield('topic_id');
		// 	$this->db->sql_freeresult($result);
		// }

		return $topic_id &&
			$this->manager->count_prefixes() &&
			$this->manager->count_prefix_instances()
		? $this->manager->load_prefixes_topic($topic_id, $block, $return_parsed)
		: '';
	}
}
