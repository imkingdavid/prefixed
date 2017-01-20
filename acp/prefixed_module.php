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
			case 'prefixes':
				$page_title = 'ACP_PREFIXED_MANAGE';
				// @todo Do this
				$action	= $request->variable('action', 'list');
				$prefix_id = $request->variable('prefix_id', 0);
				$prefix = $prefix_id ? new \imkingdavid\prefixed\core\prefix($db, $cache, $template, $prefix_id) : [];

				switch ($action)
				{
					case 'add':
					case 'edit':
						if ($submit)
						{
							$error = [];
							$prefix = [];
							foreach (['title', 'short', 'forums', 'groups', 'users'] as $prefix_key)
							{
								$prefix[$prefix_key] = $request->variable('prefix_' . $prefix_key, '', in_array($prefix_key, ['title', 'short']));
							}

							$uid = $bitfield = $options = '';
							$allow_bbcode = true;
							$allow_smilies = $allow_urls = false;
							generate_text_for_storage($prefix['title'], $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

							$prefix['bbcode_uid'] = $uid;
							$prefix['bbcode_bitfield'] = $bitfield;

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

						// If the form has not been submitted, and we're editing a prefix, prefill the form with current data
						if (!$submit && $action === 'edit')
						{
							$prefix['title'] = generate_text_for_edit($prefix['title'], $prefix['bbcode_uid'], OPTION_FLAG_BBCODE)['text'];

							$template->assign_vars([
								'PREFIX_TITLE' => isset($prefix['title']) ? $prefix['title'] : '',
								'PREFIX_SHORT' => isset($prefix['short']) ? $prefix['short'] : '',
								'PREFIX_FORUMS' => isset($prefix['forums']) ? $prefix['forums'] : '',
								'PREFIX_GROUPS' => isset($prefix['groups']) ? $prefix['groups'] : '',
								'PREFIX_USERS' => isset($prefix['users']) ? $prefix['users'] : '',
							]);
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

						if (false !== $prefixes)
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
