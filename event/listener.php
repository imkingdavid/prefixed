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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_imkingdavid_prefixed_event_listener implements EventSubscriberInterface
{
	/**
	 * DBAL object
	 * @var dbal
	 */
	private $db;

	/**
	 * Cache driver object
	 * @var phpbb_cache_driver_interface
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
	 * User object
	 * @var phpbb_user
	 */
	private $user;

	/**
	 * Prefix manager object
	 * @var phpbb_ext_imkingdavid_prefixed_core_manager
	 */
	private $manager;

	/**
	 * Table prefix
	 * @var string
	 */
	private $table_prefix;

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
			'core.viewtopic_modify_page_title'	=> 'get_viewtopic_topic_prefix',
			'core.viewforum_modify_topicrow'	=> 'get_viewforum_topic_prefixes',
			'core.posting_modify_template_vars'	=> [
				['manage_prefixes_on_posting'],
				['generate_posting_form'],
			],

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
		global $phpbb_container;

		$this->container = &$phpbb_container;

		// Let's get our table constants out of the way
		$table_prefix = $this->container->getParameter('core.table_prefix');
		define('PREFIXES_TABLE', $table_prefix . 'topic_prefixes');
		define('PREFIX_INSTANCES_TABLE', $table_prefix . 'topic_prefix_instances');

		$this->db = $this->container->get('dbal.conn');
		$this->manager = $this->container->get('prefixed.manager');
		$this->request = $this->container->get('request');
	}

	/**
	 * Get the actual data to store in the DB for given tokens
	 *
	 * @param Event $event Event object
	 * @return null
	 */
	public function get_token_data($event)
	{
		$tokens =& $event['token_data'];

		if (strpos($event['title'], '{DATE}') !== false)
		{
			$tokens['DATE'] = $this->container->get('user')->format_date(microtime(true));
		}

		if (strpos($event['title'], '{USERNAME}') !== false)
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
		$this->manager->generate_posting_form($this->request->variable('p', 0));
	}

	/**
	 * Perform given actions with given prefix IDs on the posting screen
	 *
	 * @return Event $event Event object
	 */
	public function manage_prefixes_on_posting($event)
	{
		$action = $this->request->variable('action', '');
		$ids = $this->request->variable('prefix_id', [0]);

		if (!$event['submit'] || !in_array($action, ['add', 'remove', 'remove_all']))
		{
			return;
		}

		// We treat the form as refreshed so we don't lose entered information
		$event['refresh'] = $perform_action = true;

		// If the mode is edit, we need to ensure to that we are working
		// with the first post in the topic
		if ($event['mode'] == 'edit')
		{
			$sql = 'SELECT topic_first_post_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . (int) $event['topic_id'];
			$result = $this->db->sql_query($sql);
			$first_post_id = $this->db->sql_fetchrow('topic_first_post_id');
			$this->db->sql_freeresult($result);

			if ($first_post_id != $event['post_id'])
			{
				$perform_action = false;
			}
		}

		if (!$perform_action)
		{
			return;
		}

		switch ($action)
		{
			case 'add':
				$this->manager->add_topic_prefix($event['topic_id'], $id, $event['forum_id']);
			break;

			case 'remove_all':
				$ids = 0;
			// NO break;
			case 'remove':
				$this->manager->remove_topic_prefixes($event['topic_id'], $ids);
			break;
		}

		return;
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
	public function get_viewforum_topic_prefixes($event)
	{
		$this->load_prefixes_topic($event);
	}

	/**
	 * Helper method that gets the topic prefixes for view(forum/topic) page
	 *
	 * @param Event $event Event object
	 * @return null
	 */
	protected function load_prefixes_topic($event, $array_name = 'row', $block = 'prefix')
	{
		return (
			isset($event[$array_name]['topic_id'])
			&& $this->manager->count_prefixes()
			&& $this->manager->count_prefix_instances()
		)	? $this->manager->load_prefixes_topic($event[$array_name]['topic_id'], $block) . '&nbsp;'
			: '';
	}
}
