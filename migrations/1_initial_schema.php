<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2013 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Initial schema changes needed for Extension installation
 */
class phpbb_ext_imkingdavid_prefixed_migrations_1_initial_schema extends phpbb_db_migration
{
	/**
	 * @inheritdoc
	 */
	public function update_schema()
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'topic_prefixes'	=> [
					'COLUMNS'	=> [
						'id'		=> ['UINT', NULL, 'auto_increment'],
						'title'		=> ['VCHAR_UNI', ''],
						'short'		=> ['VCHAR_UNI', ''],
						'style'		=> ['VCHAR_UNI', ''],
						'forums'	=> ['VCHAR_UNI', ''],
						'groups'	=> ['VCHAR_UNI', ''],
						'users'		=> ['VCHAR_UNI', ''],
					],
					'PRIMARY_KEY'	=> 'id',
					'KEYS'		=> [
						'short'		=> ['UNIQUE', 'short'],
						'title'		=> ['INDEX', 'title'],
					],
				],

				$this->table_prefix . 'topic_prefix_instances' => [
					'COLUMNS'	=> [
						'id'			=> ['UINT', NULL, 'auto_increment'],
						'prefix'		=> ['UINT', 0],
						'topic'			=> ['UINT', 0],
						'ordered'		=> ['UINT', 0],
						'token_data'	=> ['TEXT', ''],
					],
					'PRIMARY_KEY'	=> 'id',
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function revert_schema()
	{
		return [
			'drop_tables'	=> [
				$this->table_prefix . 'topic_prefixes',
				$this->table_prefix . 'topic_prefix_instances',
			],
		];
	}
}
