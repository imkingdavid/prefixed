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
 * Because we don't have any settings, we actually don't need a module to
 * manage said settings. Imagine that.
 */
class phpbb_ext_imkingdavid_prefixed_migrations_3_remove_settings_module extends phpbb_db_migration
{
	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return array('phpbb_ext_imkingdavid_prefixed_migrations_2_initial_data');
	}

	/**
	 * @inheritdoc
	 */
	public function update_data()
	{
		return [
			['module.remove', ['acp', 'ACP_PREFIXED_MANAGEMENT', [
					'module_basename'	=> 'phpbb_ext_imkingdavid_prefixed_acp_prefixed_module',
					'modes'				=> ['settings'],
				]
			]],
		];
	}
}
