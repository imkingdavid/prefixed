<?php

abstract class phpbb_ext_imkingdavid_prefixed_core_base
{
	/**
	 * @var int ID for the prefix
	 */
	protected $id = 0;
	/**
	 * @var dbal Database object instance
	 */
	protected dbal $db = null;
	/**
	 * @var acm Cache object instance
	 */
	private acm $cache;
	/**
	 * @var array Prefix instances
	 */
	private $all_used;
	/**
	 * @var array Prefixes
	 */
	private $all;

	public function __construct(dbal $db, $cache)
	{
		$this->db = $db;
		$this->cache = $cache;
	}

	public function load_all()
	{
		if (!empty($this->all))
		{
			return $this->all;
		}

		if (($this->all = $this->get('_prefixes')) === false)
		{
			$sql = 'SELECT id, title, short, color, users, forums
				FROM ' . PREFIXES_TABLE . '
				WHERE topic = ' . (int) $topicrow['TOPIC_ID'];
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->all[$row['id']] = array(
					'id'			=> $row['id'],
					'title'			=> $row['title'],
					'short'			=> $row['short'],
					'color'			=> $row['color'],
					'users'			=> $row['users'],
					'forums'		=> $row['forums'],
				);
			}
			$this->db->sql_freeresult($result);

			$this->put('_prefixes', $this->all);
		}

		return $this->all;
	}

	public function load_all_used()
	{
		if (!empty($this->all_used))
		{
			return $this->all_used;
		}

		if (($this->all_used = $this->get('_prefixes_used')) === false)
		{
			$sql = 'SELECT id, prefix, topic, token_data
				FROM ' . PREFIXES_USED_TABLE . '
				WHERE topic = ' . (int) $topicrow['TOPIC_ID'];
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->all_used[$row['id']] = array(
					'id'			=> $row['id'],
					'prefix'		=> $row['prefix'],
					'topic'			=> $row['topic'],
					'token_data'	=> $row['token_data'],
					'applied_time'	=> $row['applied_time'],
					'ordered'		=> $row['ordered'],
				);
			}
			$this->db->sql_freeresult($result);

			$this->put('_prefixes_used', $this->all_used);
		}

		return $this->all_used;
	}
}
