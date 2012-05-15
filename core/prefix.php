<?php

class phpbb_ext_imkingdavid_prefixed_core_prefix extends phpbb_ext_imkingdavid_prefixed_core_base
{
	private $title;
	private $short;
	private $color;
	private $users;	
	private $forums;

	public function load()
	{
		if ($this->id)
		{
			$sql = 'SELECT title, short, color, users, forums
				FROM ' . PREFIXES_TABLE . '
				WHERE id = ' . (int) $this->id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);

			$this->set('title', $row['title']);
			$this->set('short', $row['short']);
			$this->set('color', $row['color']);
			$this->set('users', $row['users']);
			$this->set('forums', $row['forums']);
		}
	}
}
