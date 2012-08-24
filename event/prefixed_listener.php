<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_imkingdavid_prefixed_event_prefixed_listener implements EventSubscriberInterface
{
	private $db;
	private $cache;
	private $template;
	private $request;
	private $table_prefix;
	private $base;

	public function __construct()
	{
		global $db, $cache, $template, $request, $table_prefix;

		// Let's get our table constants out of the way
		define('PREFIXES_TABLE', $table_prefix . 'topic_prefixes');
		define('PREFIXES_USED_TABLE', $table_prefix . 'topic_prefixes_used');

		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;
		$this->request = $request;
		$this->base = new phpbb_ext_imkingdavid_prefixed_core_base($db, $cache, $template);
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_modify_page_title'	=> 'get_viewtopic_topic_prefix',
			'core.viewforum_modify_topicrow'	=> 'get_viewforum_topic_prefixes',
			'core.modify_posting_parameters'	=> 'manage_prefixes_on_posting',
		);
	}

	public function manage_prefixes_on_posting($event)
	{
		$action = $this->request->variable('action', '');
		$id = $this->request->variable('prefix_id', '');

		if (!in_array($action, array('add', 'remove', 'remove_all')))
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
			$result = $db->sql_query($sql);
			$first_post_id = $db->sql_fetchrow('topic_first_post_id');

			if ($first_post_id != $event['post_id'])
			{
				$perform_action = false;
			}
		}

		if ($perform_action)
		{
			$this->base->perform_posting_action($action, $event['topic_id'], $id);
		}

		$this->base->generate_posting_form($event['forum_id'], $event['topic_id']);
	}

	public function get_viewtopic_topic_prefix($event)
	{
		$data = $event->get_data();

		if ($this->base->load_all() && $this->base->load_all_used())
		{
			$this->template->assign_vars(array(
				'TOPIC_PREFIX' => $this->base->load_topic_prefixes($data['topic_data']['topic_id']),
			));
			$data['page_title'] = $this->base->load_topic_prefixes($data['topic_data']['topic_id'], false) . '&nbsp;' . $data['page_title'];
		}

		$event->set_data($data);
	}

	public function get_viewforum_topic_prefixes($event)
	{
		$data = $event->get_data();
		if ($this->base->load_all() && $this->base->load_all_used())
		{
			$data['topicrow']['TOPIC_PREFIX'] = $this->base->load_topic_prefixes($data['topicrow']['TOPIC_ID']);
		}

		$event->set_data($data);
	}
}
