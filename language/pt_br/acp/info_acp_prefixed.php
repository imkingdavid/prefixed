<?php
/**
 *
 * prefixed [Brazilian Portuguese [pt_br]]
 * Brazilian Portuguese translation by eunaumtenhoid (c) 2018 [ver 1.0.0] (https://github.com/phpBBTraducoes)
 * @package language
 * @copyright (c) 2005 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'ACP_PREFIXED_MANAGEMENT'		=> 'Gerenciamento de prefixo do tópico',
	'ACP_PREFIXED_MANAGE'			=> 'Gerenciar Prefixos',
	'ACP_PREFIXED_MANAGE_EXPLAIN'	=> 'Nesta página, você pode gerenciar os prefixos do tópico para o seu fórum.',

	'ACP_PREFIXES'			=> 'Prefixos de tópicos',
	'PREFIX'				=> 'Prefixo',
	'PREFIX_PARSED'			=> 'Saída',
	'ADD_PREFIX'			=> 'Novo Prefixo',

	'PREFIX_TITLE'			=> 'Prefixo',
	'PREFIX_TITLE_EXPLAIN'	=> 'Isto é o que realmente será exibido na frente do título do tópico. BBCode é suportado. Certos tokens podem ser usados, que serão substituídos por dados reais quando o prefixo for aplicado.',
	'PREFIX_SHORT'			=> 'Nome curto',
	'PREFIX_SHORT_EXPLAIN'	=> 'Este é um identificador exclusivo para ajudá-lo a diferenciar os prefixos.',
	'PREFIX_FORUMS'			=> 'Fóruns',
	'PREFIX_FORUMS_EXPLAIN'	=> 'Especifique quais fóruns podem conter este prefixo.',
	'PREFIX_GROUPS'			=> 'Grupos',
	'PREFIX_GROUPS_EXPLAIN'	=> 'Especifique quais grupos podem aplicar esse prefixo.',
	'PREFIX_USERS'			=> 'Usuários',
	'PREFIX_USERS_EXPLAIN'	=> 'Especifique quais usuários podem aplicar esse prefixo (substitui a configuração do grupo).',

	'DELETE_PREFIX'				=> 'Tem certeza de que deseja deletarf o prefixo especificado?',
	'DELETE_PREFIX_CONFIRM'		=> 'O prefixo e todas as suas instâncias serão deletados. Isto não pode ser desfeito.',

	'PREFIX_ADDED_SUCCESS'		=> 'O prefixo foi adicionado com sucesso.',
	'PREFIX_EDITED_SUCCESS'		=> 'O prefixo foi atualizado com sucesso.',
	'PREFIX_DELETED_SUCCESS'	=> 'O prefixo foi deletado com sucesso.',
	'NO_PREFIX_ID_SPECIFIED'	=> 'Você deve especificar um ID de prefixo válido.',
]);
