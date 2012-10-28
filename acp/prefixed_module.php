<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2012 David King (imkingdavid)
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
 *
 */
class phpbb_ext_imkingdavid_prefixed_acp_prefixed_module
{
	var $u_action;
	var $new_config = [];

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		global $cache;

		$user->add_lang('acp/prefixed');
		$submit = $request->is_set_post('submit');

		$form_key = 'acp_prefixed';
		add_form_key($form_key);

		/**
		*	Validation types are:
		*		string, int, bool,
		*		script_path (absolute path in url - beginning with / and no trailing slash),
		*		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		*/
		switch ($mode)
		{
			case 'settings':
				$display_vars = [
					'title'	=> 'ACP_PREFIXED_SETTINGS',
					'vars'	=> [
						'legend1'				=> 'ACP_PREFIXED_SETTINGS',
						'sitename'				=> ['lang' => 'SITE_NAME',				'validate' => 'string',	'type' => 'text:40:255', 'explain' => false],
						'site_desc'				=> ['lang' => 'SITE_DESC',				'validate' => 'string',	'type' => 'text:40:255', 'explain' => false],
						'board_disable'			=> ['lang' => 'DISABLE_BOARD',			'validate' => 'bool',	'type' => 'custom', 'method' => 'board_disable', 'explain' => true],
						'board_disable_msg'		=> false,
						'default_lang'			=> ['lang' => 'DEFAULT_LANGUAGE',		'validate' => 'lang',	'type' => 'select', 'function' => 'language_select', 'params' => ['{CONFIG_VALUE}'], 'explain' => false],
						'default_dateformat'	=> ['lang' => 'DEFAULT_DATE_FORMAT',	'validate' => 'string',	'type' => 'custom', 'method' => 'dateformat_select', 'explain' => true],
						'board_timezone'		=> ['lang' => 'SYSTEM_TIMEZONE',		'validate' => 'string',	'type' => 'select', 'function' => 'tz_select', 'params' => ['{CONFIG_VALUE}', 1], 'explain' => true],
						'board_dst'				=> ['lang' => 'SYSTEM_DST',			'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => false],
						'default_style'			=> ['lang' => 'DEFAULT_STYLE',			'validate' => 'int',	'type' => 'select', 'function' => 'style_select', 'params' => ['{CONFIG_VALUE}', false], 'explain' => false],
						'override_user_style'	=> ['lang' => 'OVERRIDE_STYLE',		'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true],

						'legend2'				=> 'WARNINGS',
						'warnings_expire_days'	=> ['lang' => 'WARNINGS_EXPIRE',		'validate' => 'int',	'type' => 'text:3:4', 'explain' => true, 'append' => ' ' . $user->lang['DAYS']],

						'legend3'					=> 'ACP_SUBMIT_CHANGES',
					],
				];
				$this->new_config = $config;
				$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', ['' => ''], true)) : $this->new_config;
				$error = [];

				// We validate the complete config if wished
				validate_config_vars($display_vars['vars'], $cfg_array, $error);

				if ($submit && !check_form_key($form_key))
				{
					$error[] = $user->lang['FORM_INVALID'];
				}
				// Do not write values if there is an error
				if (sizeof($error))
				{
					$submit = false;
				}

				if ($submit)
				{
					foreach ($display_vars['vars'] as $config_name => $null)
					{
						if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
						{
							continue;
						}

						$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];
						set_config($config_name, $config_value);
					}
					// @todo create this language key
					add_log('admin', 'LOG_CONFIG_PREFIX_' . strtoupper($mode));

					trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
				}

				$this->tpl_name = 'acp_prefixed_settings';
				$this->page_title = $user->lang('ACP_PREFIXED_SETTINGS');

				$template->assign_vars([
					'L_TITLE'			=> $user->lang('ACP_PREFIXED_SETTINGS'),
					'L_TITLE_EXPLAIN'	=> $user->lang('ACP_PREFIXED_SETTINGS_EXPLAIN'),

					'S_ERROR'			=> sizeof($error),
					'ERROR_MSG'			=> implode('<br />', $error),

					'U_ACTION'			=> $this->u_action,
				]);

				// Output relevant page
				foreach ($display_vars['vars'] as $config_key => $vars)
				{
					if (!is_array($vars) && strpos($config_key, 'legend') === false)
					{
						continue;
					}

					if (strpos($config_key, 'legend') !== false)
					{
						$template->assign_block_vars('options', [
							'S_LEGEND'		=> true,
							'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars,
						]);

						continue;
					}

					$type = explode(':', $vars['type']);

					$l_explain = '';
					if ($vars['explain'] && isset($vars['lang_explain']))
					{
						$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
					}
					else if ($vars['explain'])
					{
						$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
					}

					$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

					if (empty($content))
					{
						continue;
					}

					$template->assign_block_vars('options', [
						'KEY'			=> $config_key,
						'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
						'S_EXPLAIN'		=> $vars['explain'],
						'TITLE_EXPLAIN'	=> $l_explain,
						'CONTENT'		=> $content,
					]);

					unset($display_vars['vars'][$config_key]);
				}
			break;

			case 'prefixes':
				// @todo Do this
				$action	= $request->variable('action', '');
				switch ($action)
				{
					case 'add':
					case 'edit':
					
					break;

					case 'delete':
					break;

					default:
					case 'list':
						$base = new phpbb_ext_imkingdavid_prefixed_core_base($db, $cache, $template, $request);

						$prefixes = $base->load_prefixes();
						foreach ($prefixes as $prefix)
						{
							$this->template->assign_block_vars('prefix', [
								'ID'		=> $prefix['id'],
								'TITLE'		=> $prefix['title'],
								'SHORT'		=> $prefix['short'],
								'STYLE'		=> $prefix['style'],
							]);
						}
					break;
				}
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}
}
