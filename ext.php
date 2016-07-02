<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2013 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace imkingdavid\prefixed;

 /**
* Extension class for custom enable/disable/purge actions
*/
class ext extends \phpbb\extension\base
{
	public function is_enableable()
	{
		$config = $this->container->get('config');
		return (version_compare($config['version'], '3.1.4', '>=') && (version_compare(PHP_VERSION, '5.4.*', '>')));
	}
}
