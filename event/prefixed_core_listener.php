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

class phpbb_ext_imkingdavid_prefixed_event_prefixed_core_listener implements EventSubscriberInterface
{
	private $db;
	private $cache;
	private $template;
	private $request;
	private $user;
	private $table_prefix;
	private $base;

	public function __construct()
	{
		global $phpbb_container;

		$this->container = &$phpbb_container;

		// Let's get our table constants out of the way
		$table_prefix = $this->container->getParameter('core.table_prefix');
		define('PREFIXES_TABLE', $table_prefix . 'topic_prefixes');
		define('PREFIX_INSTANCES_TABLE', $table_prefix . 'topic_prefix_instances');
	}

	static public function getSubscribedEvents()
	{
		return [
			// phpBB Core Events
			'core.viewtopic_modify_page_title'	=> 'get_viewtopic_topic_prefix',
			'core.viewforum_modify_topicrow'	=> 'get_viewforum_topic_prefixes',
			'core.modify_posting_parameters'	=> 'manage_prefixes_on_posting',
			'core.posting_modify_template_vars'	=> 'generate_posting_form',

			// Events added by this extension
			'prefixed.modify_prefix_title'		=> 'get_token_data',
		];
	}

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

	public function generate_posting_form($event)
	{
		$this->container->get('prefixed.base')->generate_posting_form($event['forum_id'], $event['topic_id']);
	}

	public function manage_prefixes_on_posting($event)
	{
		$action = $this->container->get('request')->variable('action', '');
		$ids = $this->container->get('request')->variable('prefix_id', [0]);

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
			$db = $this->container->get('dbal.conn');
			$sql = 'SELECT topic_first_post_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . (int) $event['topic_id'];
			$result = $db->sql_query($sql);
			$first_post_id = $db->sql_fetchrow('topic_first_post_id');
			$db->sql_freeresult($result);

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
				$this->container->get('prefixed.base')->add_topic_prefix($event['topic_id'], $id, $event['forum_id']);
			break;

			case 'remove_all':
				$ids = 0;
			// NO break;
			case 'remove':
				$this->container->get('prefixed.base')->remove_topic_prefixes($event['topic_id'], $ids);
			break;
		}

		return;
	}

	public function get_viewtopic_topic_prefix($event)
	{
		$event['page_title'] = $this->load_prefixes_topic($event, 'topic_data', 'prefix') . '&nbsp;' . $event['page_title'];
	}

	public function get_viewforum_topic_prefixes($event)
	{
		$this->load_prefixes_topic($event, 'row', 'prefix');
	}

	protected function load_prefixes_topic($event, $array_name = 'row', $block = 'prefix')
	{
		if ($this->container->get('prefixed.base')->load_prefixes() && $this->container->get('prefixed.base')->load_prefix_instances())
		{
			$this->container->get('prefixed.base')->load_prefixes_topic($event[$array_name]['topic_id'], $block);
		}
	}
}
