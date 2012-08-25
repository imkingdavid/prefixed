<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_imkingdavid_prefixed_event_prefixed_token_username_listener implements EventSubscriberInterface
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

		if (stripos($event['title'], '{USERNAME}') !== false)
		{
			$event['token_data']['USERNAME'] = $user->data['username'];
		}
	}
}
