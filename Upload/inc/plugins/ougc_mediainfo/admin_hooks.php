<?php

/***************************************************************************
 *
 *	OUGC Media Info plugin (/inc/plugins/ougc_mediainfo/admin_hooks.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2020 Omar Gonzalez
 *
 *	Website: https://ougc.network
 *
 *	Fetches films, television programs, home videos, video games, and streaming content online information to display in threads.
 *
 ***************************************************************************

****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

namespace OUGCMediaInfo\AdminHooks;

function admin_config_plugins_deactivate()
{
	global $mybb, $page;

	if(
		$mybb->get_input('action') != 'deactivate' ||
		$mybb->get_input('plugin') != 'ougc_mediainfo' ||
		!$mybb->get_input('uninstall', \MyBB::INPUT_INT)
	)
	{
		return;
	}

	if($mybb->request_method != 'post')
	{
		$page->output_confirm_action('index.php?module=config-plugins&amp;action=deactivate&amp;uninstall=1&amp;plugin=ougc_mediainfo');
	}

	if($mybb->get_input('no'))
	{
		admin_redirect('index.php?module=config-plugins');
	}
}

function admin_config_settings_start()
{
	\OUGCMediaInfo\Core\load_language();
}

function admin_style_templates_set()
{
	\OUGCMediaInfo\Core\load_language();
}

function admin_config_settings_change()
{
	global $db, $mybb;

	$query = $db->simple_select('settinggroups', 'name', "gid='{$mybb->get_input('gid', \MyBB::INPUT_INT)}'");

	!($db->fetch_field($query, 'name') == 'ougc_mediainfo') || \OUGCMediaInfo\Core\load_language();
}

function admin_config_action_handler(&$actions)
{
	$actions['ougc_mediainfo'] = [
		'active' => 'ougc_mediainfo',
		'file' => 'types.php'
	];
}

function admin_config_menu(&$items)
{
	global $lang;

	\OUGCMediaInfo\Core\load_language();

	$items[] = [
		'id' => 'ougc_mediainfo',
		'title' => $lang->ougc_mediainfo_main_menu,
		'link' => 'index.php?module=config-ougc_mediainfo'
	];
}

function admin_load()
{
	global $modules_dir, $run_module, $action_file, $run_module, $page, $modules_dir_backup, $run_module_backup, $action_file_backup;

	if($run_module != 'config' || $page->active_action != 'ougc_mediainfo')
	{
		return;
	}

	$modules_dir_backup = $modules_dir;

	$run_module_backup = $run_module;

	$action_file_backup = $action_file;

	$modules_dir = OUGC_MEDIAINFO_ROOT;

	$run_module = 'admin';

	$action_file = 'types.php';
}

function admin_config_permissions(&$args)
{
	global $lang;

	\OUGCMediaInfo\Core\load_language();

	$args['ougc_mediainfo'] = $lang->ougc_mediainfo_permissions;
}