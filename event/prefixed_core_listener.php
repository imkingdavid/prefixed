<?php

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

		// Let's get our table constants out of the way
		$table_prefix = $phpbb_container->getParameter('core.table_prefix');
		define('PREFIXES_TABLE', $table_prefix . 'topic_prefixes');
		define('PREFIX_INSTANCES_TABLE', $table_prefix . 'topic_prefix_instances');

		$this->db = $phpbb_container->get('dbal.conn');
		$this->cache = $phpbb_container->get('cache.driver');
		$this->template = $phpbb_container->get('template');
		$this->request = $phpbb_container->get('request');
		$this->user = $phpbb_container->get('user');
		$this->base = $phpbb_container->get('prefixed.base');
        $this->dispatcher = $phpbb_container->get('dispatcher');
	}

	static public function getSubscribedEvents()
	{
		return array(
			// phpBB Core Events
			'core.viewtopic_modify_page_title'	=> 'get_viewtopic_topic_prefix',
			'core.viewforum_modify_topicrow'	=> 'get_viewforum_topic_prefixes',
			'core.modify_posting_parameters'	=> 'manage_prefixes_on_posting',
			'core.posting_modify_template_vars'	=> 'generate_posting_form',

			// Events added by this extension
			'prefixed.modify_prefix_title'		=> 'get_token_data',
		);
	}

	public function get_token_data($event)
	{
		$tokens = array();

		if (strpos($event['title'], '{DATE}') !== false)
		{
			$tokens['DATE'] = time();
		}

		if (strpos($event['title'], '{USERNAME}') !== false)
		{
			$tokens['USERNAME'] = $this->user->data['username'];
		}

		$event['token_data'] = $tokens;
	}

	public function generate_posting_form($event)
	{
		$this->base->generate_posting_form($event['forum_id'], $event['topic_id']);
	}

	public function manage_prefixes_on_posting($event)
	{
		$action = $this->request->variable('action', '');
		$ids = $this->request->variable('prefix_id', array(0));

		if (!$event['submit'] || !in_array($action, array('add', 'remove', 'remove_all')))
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
				$base->add_topic_prefix($event['topic_id'], $id, $event['forum_id']);
			break;

			case 'remove_all':
				$ids = 0;
			// NO break;
			case 'remove':
				$base->remove_topic_prefixes($event['topic_id'], $ids);
			break;
		}

		return;
	}

	public function get_viewtopic_topic_prefix($event)
	{
		if ($this->base->load_prefixes() && $this->base->load_prefix_instances())
		{
			$event['page_title'] = $this->base->load_prefixes_topic($event['topic_data']['topic_id'], 'prefix') . '&nbsp;' . $event['page_title'];
		}
	}

	public function get_viewforum_topic_prefixes($event)
	{
		if ($this->base->load_prefixes() && $this->base->load_prefix_instances())
		{
			$this->base->load_prefixes_topic($event['topicrow']['TOPIC_ID'], 'topicrow.prefix');
		}
	}
}
