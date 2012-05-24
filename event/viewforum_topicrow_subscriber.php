<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_imkingdavid_prefixed_event_viewforum_topicrow_subscriber implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewforum_topicrow' => 'get_topic_prefixes',
		);
	}

	public function get_topic_prefixes(&$topicrow)
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
