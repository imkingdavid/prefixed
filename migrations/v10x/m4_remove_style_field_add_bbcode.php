<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2013 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace imkingdavid\prefixed\migrations\v10x;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * I'm actually going to let you use BBCode instead of raw styles. It's easier
 * for both of us this way.
 */
class m4_remove_style_field_add_bbcode extends \phpbb\db\migration\migration
{
	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return array('\imkingdavid\prefixed\migrations\v10x\m3_remove_settings_module');
	}

	/**
	 * @inheritdoc
	 */
	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'topic_prefixes' => [
					'bbcode_uid' => ['VCHAR_UNI', ''],
					'bbcode_bitfield' => ['VCHAR_UNI', ''],
				],
			],
			'drop_columns' => [
				$this->table_prefix . 'topic_prefixes' => [
					'style',
				],
			],
		];
	}
}
