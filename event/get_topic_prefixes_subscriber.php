<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_imkingdavid_prefixed_event_viewtopic_page_header_subscriber implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_page_header' => 'get_viewtopic_topic_prefix',
			'core.viewforum_topicrow' => 'get_viewforum_topic_prefixes',
		);
	}

	public function get_viewtopic_topic_prefix($topic_data)
	{
		global $db, $cache, $template;

		$core = new phpbb_ext_imkingdavid_prefixed_core_base($db, $cache);
		
		if ($core->load_all() && $core->load_all_used())
		{
			$template->assign_vars(array(
				'TOPIC_PREFIX' 		=> $core->load_topic_prefixes($topic_data['topic_id']),
			));
		}
	}

	public function get_viewforum_topic_prefixes(&$topicrow)
	{
		global $db, $cache;

		$core = new phpbb_ext_imkingdavid_prefixed_core_base($db, $cache);
		
		// We want to make sure there are prefixes and they have been used.
		// Otherwise, we don't need to do anything.
		if ($core->load_all() && $core->load_all_used())
		{
			$topicrow['TOPIC_PREFIX'] = $core->load_topic_prefixes($topicrow['topic_id']);
		}
	}
}
