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
 * Initial data changes needed for Extension installation
 */
class phpbb_ext_imkingdavid_prefixed_migrations_2_initial_data extends phpbb_db_migration
{
	/**
	 * @inheritdoc
	 */
	public static function update_data()
	{
		return [
			// We'll use this to keep track of the extension version in the DB
			['config.add'	=> ['prefixed_version', '1.0.0a1']],
		];
	}
}
