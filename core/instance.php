<?php

class phpbb_ext_imkingdavid_prefixed_core_instance extends phpbb_ext_imkingdavid_prefixed_core_base
{
	/**
	 * @var int Prefix ID
	 */
	private $prefix;
	/**
	 * @var string Topic ID
	 */
	private $topic;
	/**
	 * @var string Serialized token array
	 */
	private $token_data;
	/**
	 * @var array Token array
	 */
	private $tokens;

	public function load()
	{
		if ($this->id)
		{
			$sql = 'SELECT prefix, topic, token_data
				FROM ' . PREFIXES_USED_TABLE . '
				WHERE id = ' . (int) $this->id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			$this->set('prefix', (int) $row['prefix']);
			$this->set('topic', $row['topic']);
			$this->set('token_data', $row['token_data']);
			$this->set('tokens', unserialize($this->token_data));
		}
	}

}
