<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_imkingdavid_prefixed_event_viewforum_topicrow_subscriber implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewforum_topicrow' => 'apply_prefix',
		);
	}

	public function apply_prefix(&$topicrow)
	{
		global $db, $cache;
		$core = new phpbb_ext_imkingdavid_prefixed_core_base($db, $cache);
		$prefixes = array();

		// We want to make sure there are prefixes and they have been used.
		// Otherwise, we don't need to do anything.
		if ($core->load_all() && $core->load_all_used())
		{
			foreach ($core->all_used AS $id => $data)
			{
				if ($data['topic'] == $topicrow['TOPIC_ID'])
				{
					$prefix = new phpbb_ext_imkingdavid_prefixed_core_instance($db, $cache, $id);
					$prefix->load();
					$prefixes[$prefix->ordered] = $prefix->parse();
				}
			}
		}

		$topicrow['TOPIC_PREFIX'] = implode('', $prefixes);
	}
}
