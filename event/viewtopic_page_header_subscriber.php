<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_imkingdavid_prefixed_event_viewtopic_page_header_subscriber implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_page_header' => 'get_topic_prefix',
		);
	}

	public function get_topic_prefix(&$topic_data)
	{
		global $db, $cache, $template;

		$core = new phpbb_ext_imkingdavid_prefixed_core_blah($db, $cache, $topic_data['topic_id']);

		$topic_prefix = $core->$get_topic_prefix;

		$template->assign_vars(array(
			'TOPIC_PREFIX' 		=> $topic_prefix,
		));
	}
}
