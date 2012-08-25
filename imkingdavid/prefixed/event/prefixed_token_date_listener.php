<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_imkingdavid_prefixed_event_prefixed_token_date_listener implements EventSubscriberInterface
{
	private $user;

	public function __construct()
	{
		global $user;
		$this->user = $user;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'prefixed.modify_prefix_title'	=> 'get_token_data',
		);
	}

	public function get_token_data($event)
	{
		$title = $event['title'];

		if (stripos($event['title'], '{DATE}') !== false)
		{
			$event['token_data']['DATE'] = time();
		}
	}
}
