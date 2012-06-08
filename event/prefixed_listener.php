<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_imkingdavid_prefixed_event_prefixed_listener implements EventSubscriberInterface
{
	private $db;
	private $cache;
	private $template;
	private $base;

	public function __construct()
	{
		global $db, $cache, $template, $table_prefix;

		// Let's get our table constants out of the way
		define('PREFIXES_TABLE', $table_prefix . 'topic_prefixes');
		define('PREFIXES_USED_TABLE', $table_prefix . 'topic_prefixes_used');

		$this->db = $db;
		$this->cache = $cache;
		$this->template = $template;
		$this->base = new phpbb_ext_imkingdavid_prefixed_core_base($db, $cache);
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_page_header' => 'get_viewtopic_topic_prefix',
			'core.viewforum_topicrow' => 'get_viewforum_topic_prefixes',
		);
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
