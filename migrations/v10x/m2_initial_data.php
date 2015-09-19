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
 * Initial data changes needed for Extension installation
 */
class m2_initial_data extends \phpbb\db\migration\migration
{
	/**
	 * @inheritdoc
	 */
	public function effectively_installed()
	{
		return isset($this->config['prefixed_version']) && version_compare($this->config['prefixed_version'], '1.0.0a1', '>=');
	}

	/**
	 * @inheritdoc
	 */
	static public function depends_on()
	{
		return ['\imkingdavid\prefixed\migrations\v10x\m1_initial_schema'];
	}

	/**
	 * @inheritdoc
	 */
	public function update_data()
	{
		return [
			// We'll use this to keep track of the extension version in the DB
			['config.add', ['prefixed_version', '1.0.0a1']],

			['module.add', ['acp', 'ACP_CAT_POSTING', 'ACP_PREFIXED_MANAGEMENT']],
			['module.add', ['acp', 'ACP_PREFIXED_MANAGEMENT', [
					'module_basename'	=> '\imkingdavid\prefixed\acp\prefixed_module',
					'modes'				=> ['settings', 'prefixes'],
			]]],
		];
	}
}
