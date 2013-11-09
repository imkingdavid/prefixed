<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2013 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace imkingdavid\prefixed\core\token;

/**
* Prefixed token exception
*
* @package prefixed
*/
class exception extends \Exception
{
	public function __toString()
	{
		return $this->getMessage();
	}
}
