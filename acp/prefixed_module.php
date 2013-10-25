<?php
/**
 *
 * @package prefixed
 * @copyright (c) 2013 David King (imkingdavid)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace imkingdavid\prefixed\acp;

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
class prefixed_module
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
		$manager = $phpbb_container->get('prefixed.manager');

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
				$prefix_id = $request->variable('prefix_id', 0);
				$prefix = $prefix_id ? new \imkingdavid\prefixed\core\prefix($db, $cache, $template, $prefix_id) : array();

				switch ($action)
				{
					case 'add':
					case 'edit':
						if ($submit)
						{
							$error = [];
							$prefix = [];
							$prefix['title'] = $request->variable('prefix_title', '', true);
							$prefix['short'] = $request->variable('prefix_short', '');
							$prefix['style'] = $request->variable('prefix_style', '', true);
							$prefix['forums'] = $request->variable('prefix_forums', '');
							$prefix['groups'] = $request->variable('prefix_groups', '');
							$prefix['users'] = $request->variable('prefix_users', '');

							if (!sizeof($error))
							{
								// Get the style into JSON format by creating an
								// array and running json_encode()
								$css_attributes = explode(';', $prefix['style']);
								$style = array();
								foreach ($css_attributes as $attribute)
								{
									if (empty($attribute) || strpos($attribute, ':') === false)
									{
										continue;
									}
									$attr = explode(':', $attribute);
									$style[$attr[0]] = $attr[1];
								}
								$prefix['style'] = json_encode($style);

								// Update or insert the prefix in the database
								if ($action === 'add')
								{
									$sql = 'INSERT INTO ' . PREFIXES_TABLE . ' ' . $db->sql_build_array('INSERT', $prefix);
								}
								else
								{
									$sql = 'UPDATE ' . PREFIXES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $prefix) .'
										WHERE id = ' . (int) $prefix_id;
								}
								$db->sql_query($sql);

								$manager->clear_prefix_cache();
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

						$style = json_decode($prefix['style']);
						$prefix['style'] = '';
						foreach ($style as $key => $value)
						{
							$value = trim($value);
							$prefix['style'] .= "$key:$value;";
						}

						$template->assign_vars([
							'PREFIX_TITLE' => isset($prefix['title']) ? $prefix['title'] : '',
							'PREFIX_SHORT' => isset($prefix['short']) ? $prefix['short'] : '',
							'PREFIX_STYLE' => isset($prefix['style']) ? $prefix['style'] : '',
							'PREFIX_FORUMS' => isset($prefix['forums']) ? $prefix['forums'] : '',
							'PREFIX_GROUPS' => isset($prefix['groups']) ? $prefix['groups'] : '',
							'PREFIX_USERS' => isset($prefix['users']) ? $prefix['users'] : '',
						]);
						
						if (!$submit && $action === 'edit')
						{
							// Set the form fields to the corresponding values
							// from the database
							$prefix = new \imkingdavid\prefixed\core\prefix($db, $cache, $template, $prefix_id);
							$prefix->parse('', [], 'PREFIX_');
						}
					break;

					case 'delete':
						if ($prefix_id)
						{
							if ($prefix->exists())
							{
								if (confirm_box(true))
								{
									// Format: table => prefix ID column
									$tables = [
										PREFIX_INSTANCES_TABLE => 'prefix',
										PREFIXES_TABLE => 'id',
									];

									foreach ($tables as $table => $column)
									{
										// Delete all instances of this prefix
										$sql = 'DELETE FROM ' . $table . '
											WHERE ' . $column . ' = ' . (int) $prefix_id;
										$db->sql_query($sql);
									}

									$manager->clear_prefix_cache();
									trigger_error('PREFIX_DELETED_SUCCESS');
								}
								else
								{
									$s_hidden_fields = build_hidden_fields([
										'submit'    => true,
									]);

									//display mode
									confirm_box(false, 'DELETE_PREFIX', $s_hidden_fields);
								}
							}
						}

						trigger_error('NO_PREFIX_ID_SPECIFIED');
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
								$object = (new \imkingdavid\prefixed\core\prefix(
									$db, $cache, $template, (int) $prefix['id']
								))->parse('prefix', [
									'U_DELETE'	=> $this->u_action . '&amp;action=delete&amp;prefix_id=' . $prefix['id'],
									'U_EDIT'	=> $this->u_action . '&amp;action=edit&amp;prefix_id=' . $prefix['id']
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
