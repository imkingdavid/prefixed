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
			'core.user_setup'							=> ['setup', 1],
			'core.viewtopic_modify_page_title'			=> 'get_viewtopic_topic_prefix',
			'core.viewforum_modify_topicrow'			=> 'get_topiclist_topic_prefixes',
			'core.submit_post_end'						=> 'manage_prefixes_on_posting',
			'core.posting_modify_template_vars'			=> 'generate_posting_form',
			'core.mcp_view_forum_modify_topicrow'		=> 'get_topiclist_topic_prefixes',
			'core.viewforum_get_topic_ids_data'			=> 'filter_viewforum_by_prefix',
			'core.display_forums_modify_sql'			=> 'modify_forumlist_sql',
			'core.display_forums_modify_template_vars'	=> 'get_forumlist_topic_prefixes',
			'core.search_modify_tpl_ary'				=> 'get_searchlist_topic_prefixes', 

			// Events added by this extension
			'prefixed.modify_prefix_title'				=> 'get_token_data',
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
				WHERE topic_id = ' . (int) $event['data']['topic_id'];
			$result = $this->db->sql_query($sql);
			$first_post_id = $this->db->sql_fetchfield('topic_first_post_id');
			$this->db->sql_freeresult($result);

			if ((int) $event['data']['post_id'] !== (int) $first_post_id)
			{
				return;
			}
		} elseif ('post' !== $event['mode']) {
			return;
		}

		$this->manager->set_topic_prefixes((int) $event['data']['topic_id'], $this->manager->get_submitted_prefixes(), (int) $event['data']['forum_id']);
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
		$topic_prefixes = $this->load_prefixes_topic($event, 'topic_data');
		$event['page_title'] = ($topic_prefixes ? $topic_prefixes . ' ' : '') .  $event['page_title'];
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
	 * Show the prefixes in search pages
	 *
	 * Credit to user Goztow:
	 * https://www.phpbb.com/community/viewtopic.php?p=14306876#p14306876
	 *
	 * @param Event #event Event object
	 * @return null
	 */
	public function get_searchlist_topic_prefixes($event)
	{
		$tpl_ary = $event['tpl_ary']; // Template variables
		$prefixes = $this->load_prefixes_topic($event, 'row', '', true);
		$tpl_ary['TOPIC_TITLE'] = $prefixes . $tpl_ary['TOPIC_TITLE'];
		/*
		Because it is a topic prefix, rather than a post prefix,
		I have made the decision not to include the following in the official extension
		I may make an ACP setting to let you choose if the post subject should show the
		prefix as well, but for now I am not including it.

		if (!empty($tpl_ary['POST_SUBJECT']))
		{
			$tpl_ary['POST_SUBJECT'] = $prefixes . $tpl_ary['POST_SUBJECT'];
		}*/
		$event['tpl_ary'] = $tpl_ary;
	}

	/**
	 * Get the parsed prefix for each of the last posts
	 *
	 * @param Event $event Event object
	 * @return null
	 */
	public function get_forumlist_topic_prefixes($event)
	{
		$forum_row = $event['forum_row'];
		$forum_row['TOPIC_PREFIX'] = $this->load_prefixes_topic($event, 'row', '', true);
		$event['forum_row'] = $forum_row;
	}

	/**
	 * Allow showing only topics with the given prefix in viewforum
	 *
	 * @var Event $event
	 */
	public function filter_viewforum_by_prefix($event)
	{
		if ($prefix = $this->request->variable('prefix', 0)) {
			$sql_ary = $event['sql_ary'];
			$sql_ary['LEFT_JOIN'] = [[
				'FROM'  => array(PREFIX_INSTANCES_TABLE => 'pr'),
				'ON'    => 'pr.topic = t.topic_id',
			]];
			$sql_ary['WHERE'] .= 'AND pr.prefix = ' . (int) $prefix . ' ';
			$event['sql_ary'] = $sql_ary;
		}
	}

	/**
	 * Alter the SQL performed when forums are loaded
	 *
	 * @param Event $event
	 * @return null
	 */
	public function modify_forumlist_sql($event)
	{
		$sql = $event['sql_ary'];
		$sql['SELECT'] .= ', t.topic_id AS forum_last_post_topic_id';
		$sql['LEFT_JOIN'][] = [
			'FROM' => [POSTS_TABLE => 'p'],
			'ON' => 'f.forum_last_post_id = p.post_id',
		];
		$sql['LEFT_JOIN'][] = [
			'FROM' => [TOPICS_TABLE => 't'],
			'ON' => 't.topic_id = p.topic_id',
		];

		$event['sql_ary'] = $sql;
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
		else if (isset($event[$array_name]['forum_last_post_id']))
		{
			$topic_id = (int) $event[$array_name]['forum_last_post_topic_id'];
		}

		return $topic_id && $this->manager->count_prefixes() && $this->manager->count_prefix_instances()
			? $this->manager->load_prefixes_topic($topic_id, $block, $return_parsed)
			: ''
		;
	}
}
