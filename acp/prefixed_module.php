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
 *
 */
class phpbb_ext_imkingdavid_prefixed_acp_prefixed_module
{
	var $u_action;
	var $new_config = [];

	function main($id, $mode)
	{
		global $phpbb_container;
		global $phpbb_admin_path, $phpEx;
		$db = $phpbb_container->get('dbal.conn');
		$user = $phpbb_container->get('user');
		$auth = $phpbb_container->get('auth');
		$template = $phpbb_container->get('template');
		$config = $phpbb_container->get('config');
		$cache = $phpbb_container->get('cache.driver');
		$request = $phpbb_container->get('request');

		$submit = (bool) $request->is_set_post('submit');
		$prefix_id = (int) $request->variable('prefix_id', 0);

		$form_key = 'acp_prefixed';
		add_form_key($form_key);

		switch ($mode)
		{
			default:
			case 'prefixes':
				$page_title = 'ACP_PREFIXED_MANAGE';
				// @todo Do this
				$action	= $request->variable('action', 'list');
				$manager = new phpbb_ext_imkingdavid_prefixed_core_manager($db, $cache, $template, $request);

				switch ($action)
				{
					case 'add':
					case 'edit':
						if ($submit)
						{
							$error = [];
							$prefix['title'] = $request->variable('prefix_title', '');
							$prefix['short'] = $request->variable('prefix_short', '');
							$prefix['style'] = $request->variable('prefix_style', '');
							$prefix['forums'] = $request->variable('prefix_forums', '');
							$prefix['groups'] = $request->variable('prefix_groups', '');
							$prefix['users'] = $request->variable('prefix_users', '');
							if (!sizeof($error))
							{
								// Update or insert the prefix in the database
								if ($action === 'add')
								{
									$sql = 'INSERT INTO ' . PREFIXES_TABLE . ' ' . $db->sql_build_array('INSERT', $prefix);
								}
								else
								{
									$sql = 'UPDATE ' . PREFIXES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $prefix) .'
										WHERE prefix_id = ' . (int) $prefix_id;
								}
								$db->sql_query($sql);

								// Show the message
								trigger_error($user->lang('PREFIX_' . strtoupper($action) . 'ED_SUCCESS') .
									adm_back_link($this->u_action));
							}
						}
						

						// If the form was submitted and there was an error
						// or the form was not submitted and we are editing
						if ($submit && sizeof($error))
						{
							$error = '<ul><li>' . implode('</li><li>', $error) . '</li></ul>';
							$template->assign_vars([
								'S_ERROR'	=> true,
								'ERROR_MSG'	=> $error,
							]);
						}
						else if ($action === 'edit')
						{
							// Set the form fields to the corresponding values
							// from the database
							$prefix = new phpbb_ext_imkingdavid_prefixed_core_prefix($db, $cache, $template, $prefix_id);
						}

						$template->assign_vars([
							'PREFIX_TITLE' => isset($prefix['title']) ? $prefix['title'] : '',
							'PREFIX_SHORT' => isset($prefix['short']) ? $prefix['short'] : '',
							'PREFIX_STYLE' => isset($prefix['style']) ? $prefix['style'] : '',
							'PREFIX_FORUMS' => isset($prefix['forums']) ? $prefix['forums'] : '',
							'PREFIX_GROUPS' => isset($prefix['groups']) ? $prefix['groups'] : '',
							'PREFIX_USERS' => isset($prefix['users']) ? $prefix['users'] : '',
						]);
					break;

					case 'delete':
					break;

					default:
					case 'list':
						$template->assign_vars([
							'S_LIST'	=> true,
							'U_ACTION'	=> $this->u_action . '&amp;action=add',
						]);
						$prefixes = $manager->get_prefixes();
						if ($prefixes !== false)
						{
							foreach ($prefixes as $prefix)
							{
								$object = (new phpbb_ext_imkingdavid_prefixed_core_prefix(
									$db, $cache, $template, (int) $prefix['id']
								))->parse('prefix', [
									'U_DELETE'	=> $this->u_action . '&amp;action=delete&amp;prefix_id=' . $row['bbcode_id'],
									'U_EDIT'	=> $this->u_action . '&amp;action=edit&amp;prefix_id=' . $row['bbcode_id']
								]);
							}
						}
					break;
				}
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		// Set up the page
		$this->tpl_name = 'acp_prefixed';
		$this->page_title = $user->lang('ACP_PREFIXED_MANAGE');
		$template->assign_vars([
			'L_TITLE'	=> $this->page_title,
			'L_TITLE_EXPLAIN' => $user->lang('ACP_PREFIXED_MANAGE_EXPLAIN'),
		]);
	}
}
