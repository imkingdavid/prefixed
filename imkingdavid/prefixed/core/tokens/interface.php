<?php

interface phpbb_ext_imkingdavid_prefixed_core_tokens_interface
{
	/**
	 * Retrieve token data array
	 *
	 * Example array structure:
	 *	array(
	 *		'token_name'	=> 'USERNAME', // Will be {USERNAME} in the prefix
	 *	);
	 *
	 * @return array
	 */
	public function load_data();
}
