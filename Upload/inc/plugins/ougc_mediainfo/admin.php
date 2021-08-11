<?php

/***************************************************************************
 *
 *	OUGC Media Info plugin (/inc/plugins/ougc_mediainfo/admin.php)
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

namespace OUGCMediaInfo\Admin;

function _info()
{
	global $lang;

	\OUGCMediaInfo\Core\load_language();

	return [
		'name'			=> 'OUGC Media Info',
		'description'	=> $lang->setting_group_ougc_mediainfo_desc,
		'website'		=> 'https://ougc.network',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'https://ougc.network',
		'version'		=> '1.8.11',
		'versioncode'	=> 1811,//UPDATE//
		'compatibility'	=> '18*',
		'codename'		=> 'ougc_mediainfo',
		'pl'			=> [
			'version'	=> 13,
			'url'		=> 'https://community.mybb.com/mods.php?action=view&pid=573'
		]
	];
}

function _activate()
{
	global $PL, $lang, $mybb;

	\OUGCMediaInfo\Core\load_pluginlibrary();

	$PL->settings('ougc_mediainfo', $lang->setting_group_ougc_mediainfo, $lang->setting_group_ougc_mediainfo_desc, array(
		'forums'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_forums,
		   'description'	=> $lang->setting_ougc_mediainfo_forums_desc,
		   'optionscode'	=> 'forumselect',
		   'value'			=> -1
		),
		'apikey'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_apikey,
		   'description'	=> $lang->setting_ougc_mediainfo_apikey_desc,
		   'optionscode'	=> 'text',
		   'value'			=> ''
		),
		'tmdbapikey'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_tmdbapikey,
		   'description'	=> $lang->setting_ougc_mediainfo_tmdbapikey_desc,
		   'optionscode'	=> 'text',
		   'value'			=> ''
		),
		'fields_thread'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_fields_thread,
		   'description'	=> $lang->setting_ougc_mediainfo_fields_thread_desc,
		   'optionscode'	=> "checkbox
title={$lang->setting_ougc_mediainfo_fields_title}
year={$lang->setting_ougc_mediainfo_fields_year}
released={$lang->setting_ougc_mediainfo_fields_released}
runtime={$lang->setting_ougc_mediainfo_fields_runtime}
genre={$lang->setting_ougc_mediainfo_fields_genre}
director={$lang->setting_ougc_mediainfo_fields_director}
writer={$lang->setting_ougc_mediainfo_fields_writer}
actors={$lang->setting_ougc_mediainfo_fields_actors}
plot={$lang->setting_ougc_mediainfo_fields_plot}
language={$lang->setting_ougc_mediainfo_fields_language}
country={$lang->setting_ougc_mediainfo_fields_country}
awards={$lang->setting_ougc_mediainfo_fields_awards}
type={$lang->setting_ougc_mediainfo_fields_type}
production={$lang->setting_ougc_mediainfo_fields_production}
metascore={$lang->setting_ougc_mediainfo_fields_metascore}
imdbvotes={$lang->setting_ougc_mediainfo_fields_imdbvotes}
rated={$lang->setting_ougc_mediainfo_fields_rated}
rating_list={$lang->setting_ougc_mediainfo_fields_rating_list}",
		   'value'			=> 'title,year,released,runtime,genre,director,writer,actors,plot,language,country,awards,type,production,metascore,imdbvotes,rated,rating_list'
		),
		'fields_forumlist'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_fields_forumlist,
		   'description'	=> $lang->setting_ougc_mediainfo_fields_forumlist_desc,
		   'optionscode'	=> "checkbox
title={$lang->setting_ougc_mediainfo_fields_title}
year={$lang->setting_ougc_mediainfo_fields_year}
released={$lang->setting_ougc_mediainfo_fields_released}
runtime={$lang->setting_ougc_mediainfo_fields_runtime}
genre={$lang->setting_ougc_mediainfo_fields_genre}
director={$lang->setting_ougc_mediainfo_fields_director}
writer={$lang->setting_ougc_mediainfo_fields_writer}
actors={$lang->setting_ougc_mediainfo_fields_actors}
plot={$lang->setting_ougc_mediainfo_fields_plot}
language={$lang->setting_ougc_mediainfo_fields_language}
country={$lang->setting_ougc_mediainfo_fields_country}
awards={$lang->setting_ougc_mediainfo_fields_awards}
type={$lang->setting_ougc_mediainfo_fields_type}
production={$lang->setting_ougc_mediainfo_fields_production}
metascore={$lang->setting_ougc_mediainfo_fields_metascore}
imdbvotes={$lang->setting_ougc_mediainfo_fields_imdbvotes}
rated={$lang->setting_ougc_mediainfo_fields_rated}
rating_list={$lang->setting_ougc_mediainfo_fields_rating_list}",
		   'value'			=> 'released,genre,director,country,awards,type,production'
		),
		'enablesearch'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_enablesearch,
		   'description'	=> $lang->setting_ougc_mediainfo_enablesearch_desc,
		   'optionscode'	=> 'yesno',
		   'value'			=> 1
		),
		'fetchfrommessage'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_fetchfrommessage,
		   'description'	=> $lang->setting_ougc_mediainfo_fetchfrommessage_desc,
		   'optionscode'	=> 'yesno',
		   'value'			=> 0
		),
		'forceinput'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_forceinput,
		   'description'	=> $lang->setting_ougc_mediainfo_forceinput_desc,
		   'optionscode'	=> 'yesno',
		   'value'			=> 1
		),
		'allowmycode'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_allowmycode,
		   'description'	=> $lang->setting_ougc_mediainfo_allowmycode_desc,
		   'optionscode'	=> 'yesno',
		   'value'			=> 0
		),
		'mycodetag'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_mycodetag,
		   'description'	=> $lang->setting_ougc_mediainfo_mycodetag_desc,
		   'optionscode'	=> 'text',
		   'value'			=> 'mediainfo'
		),
		'enablemanual'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_enablemanual,
		   'description'	=> $lang->setting_ougc_mediainfo_enablemanual_desc,
		   'optionscode'	=> 'yesno',
		   'value'			=> 1
		),
		'posterimage'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_posterimage,
		   'description'	=> $lang->setting_ougc_mediainfo_posterimage_desc,
		   'optionscode'	=> "radio
imdb={$lang->setting_ougc_mediainfo_posterimage_imdb}
tmdb={$lang->setting_ougc_mediainfo_posterimage_tmdb}",
		   'value'			=> 'imdb'
		),
		'posterimage_height'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_posterimage_height,
		   'description'	=> $lang->setting_ougc_mediainfo_posterimage_height_desc,
		   'optionscode'	=> 'numeric',
		   'value'			=> 800
		),
		'posterimage_width'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_posterimage_width,
		   'description'	=> $lang->setting_ougc_mediainfo_posterimage_width_desc,
		   'optionscode'	=> 'numeric',
		   'value'			=> 600
		),
		'posterimage_size'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_posterimage_size,
		   'description'	=> $lang->setting_ougc_mediainfo_posterimage_size_desc,
		   'optionscode'	=> "radio
original=original
w92=w92
w154=w154
w342=w342
w500=w500
w780=w780",
		   'value'			=> 'original'
		),
		'storeimage'				=> array(
		   'title'			=> $lang->setting_ougc_mediainfo_storeimage,
		   'description'	=> $lang->setting_ougc_mediainfo_storeimage_desc,
		   'optionscode'	=> 'yesno',
		   'value'			=> 1
		),
	));

	// Add templates
	$templatesDirIterator = new \DirectoryIterator(OUGC_MEDIAINFO_ROOT.'/templates');

	$templates = [];

	foreach($templatesDirIterator as $template)
	{
		if(!$template->isFile())
		{
			continue;
		}

		$pathName = $template->getPathname();

		$pathInfo = pathinfo($pathName);

		if($pathInfo['extension'] === 'html')
		{
			$templates[$pathInfo['filename']] = file_get_contents($pathName);
		}
	}

	if($templates)
	{
		$PL->templates('ougcmediainfo', 'OUGC Media Info', $templates);
	}

	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

	find_replace_templatesets('newthread', '#'.preg_quote('{$posticons}').'#i', '{$ougc_mediainfo_input}{$posticons}');
	find_replace_templatesets('editpost', '#'.preg_quote('{$posticons}').'#i', '{$ougc_mediainfo_input}{$posticons}');
	find_replace_templatesets('showthread', '#'.preg_quote('<tr><td id="posts_container">').'#i', '{$ougc_mediainfo_display}<tr><td id="posts_container">');
	find_replace_templatesets('forumdisplay_thread', '#'.preg_quote('<td class="{$bgcolor}{$thread_type_class}">').'#i', '<td class="{$bgcolor}{$thread_type_class}"{$ougc_mediainfo_id}>');
	find_replace_templatesets('forumdisplay', '#'.preg_quote('{$threadslist}').'#i', '{$threadslist}{$ougc_mediainfo_forumdisplay_js}');
	find_replace_templatesets('forumdisplay_thread', '#'.preg_quote('{$attachment_count}').'#i', '{$attachment_count}{$ougc_mediainfo_popup}');;
	find_replace_templatesets('search', '#'.preg_quote('search_titles_only}</span></td>').'#i', 'search_titles_only}{$ougc_mediainfo_search}</span></td>');

	// Insert/update version into cache
	$plugins = $mybb->cache->read('ougc_plugins');

	if(!$plugins)
	{
		$plugins = array();
	}

	$pluginInfo = ougc_mediainfo_info();

	if(!isset($plugins['mediainfo']))
	{
		$plugins['mediainfo'] = $pluginInfo['versioncode'];
	}

	_db_verify_tables();

	_db_verify_columns();

	_db_verify_indexes();

	/*~*~* RUN UPDATES START *~*~*/

	if($plugins['mediainfo'] <= 1810)
	{
		global $db;
		global $ougc_mediainfo;

		$query = $db->simple_select('ougc_mediainfo', 'mid,imdbid', "tmdbid=''");

		while($thread = $db->fetch_array($query))
		{
			$ougc_mediainfo->get_tmdbid_by_imdbid($thread['imdbid']);

			if(!$ougc_mediainfo->tmdbid)
			{
				continue;
			}

			$db->update_query('ougc_mediainfo', ['tmdbid' => $ougc_mediainfo->tmdbid], "mid='{$thread['mid']}'");
		}
	}

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['mediainfo'] = $pluginInfo['versioncode'];

	$mybb->cache->update('ougc_plugins', $plugins);
}

function _deactivate()
{
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

	find_replace_templatesets('newthread', '#'.preg_quote('{$ougc_mediainfo_input}').'#i', '', 0);

	find_replace_templatesets('editpost', '#'.preg_quote('{$ougc_mediainfo_input}').'#i', '', 0);

	find_replace_templatesets('showthread', '#'.preg_quote('{$ougc_mediainfo_display}').'#i', '', 0);

	find_replace_templatesets('forumdisplay_thread', '#'.preg_quote('{$ougc_mediainfo_id}').'#i', '', 0);

	find_replace_templatesets('forumdisplay', '#'.preg_quote('{$ougc_mediainfo_forumdisplay_js}').'#i', '', 0);

	find_replace_templatesets('forumdisplay_thread', '#'.preg_quote('{$ougc_mediainfo_popup}').'#i', '', 0);

	find_replace_templatesets('search', '#'.preg_quote('{$ougc_mediainfo_search}').'#i', '', 0);
}

function _install()
{
	_db_verify_tables();

	_db_verify_columns();

	_db_verify_indexes();
}

function _is_installed()
{
	global $db;

	static $installed = null;

	if($installed === null)
	{
		foreach(_db_tables() as $name => $table)
		{
			$installed = $db->table_exists($name);
	
			break;
		}
	}

	return $installed;
}

function _uninstall()
{
	global $PL, $cache, $db;

	\OUGCMediaInfo\Core\load_pluginlibrary();

	// Drop DB entries
	foreach(_db_tables() as $name => $table)
	{
		$db->drop_table($name);
	}

	foreach(_db_columns() as $table => $columns)
	{
		foreach($columns as $name => $definition)
		{
			!$db->field_exists($name, $table) || $db->drop_column($table, $name);
		}
	}

	// Delete settings
	$PL->settings_delete('ougc_mediainfo');

	$PL->templates_delete('ougcmediainfo');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['mediainfo']))
	{
		unset($plugins['mediainfo']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$cache->delete('ougc_plugins');
	}

	$cache->delete('ougc_mediainfo_categories');
}

// List of tables
function _db_tables()
{
	return [
		'ougc_mediainfo'	=> [
			'mid'			=> "int UNSIGNED NOT NULL AUTO_INCREMENT",
			'title'			=> "varchar(150) NOT NULL DEFAULT ''",
			'year'			=> "int(5) NOT NULL DEFAULT '0'",
			'rated'			=> "varchar(10) NOT NULL DEFAULT ''",
			'released'		=> "int(10) NOT NULL DEFAULT '0'",
			'runtime'		=> "varchar(10) NOT NULL DEFAULT ''",
			'genre'			=> "varchar(250) NOT NULL DEFAULT ''",
			'director'		=> "varchar(150) NOT NULL DEFAULT ''",
			'writer'		=> "text NULL",
			'actors'		=> "text NULL",
			'plot'			=> "text NULL",
			'language'		=> "varchar(250) NOT NULL DEFAULT ''",
			'country'		=> "varchar(150) NOT NULL DEFAULT ''",
			'awards'		=> "varchar(150) NOT NULL DEFAULT ''",
			'poster'		=> "varchar(200) NOT NULL DEFAULT ''",
			'ratings'		=> "text NULL",
			'metascore'		=> "tinyint(5) NOT NULL DEFAULT '0'",
			'imdbrating'	=> "float(4,2) UNSIGNED NOT NULL DEFAULT '0.00'",
			'imdbvotes'		=> "int(10) NOT NULL DEFAULT '0'",
			'imdbid'		=> "varchar(15) NOT NULL DEFAULT ''",
			'tmdbid'		=> "int NOT NULL DEFAULT '0'",
			'type'			=> "varchar(15) NOT NULL DEFAULT ''",
			'production'	=> "varchar(50) NOT NULL DEFAULT ''",
			'image'		=> "varchar(150) NOT NULL DEFAULT ''",
			'primary_key'	=> "mid",
			'unique_key' => ['imdbid' => 'imdbid'],
		],
		'ougc_mediainfo_categories'	=> [
			'cid'			=> "int UNSIGNED NOT NULL AUTO_INCREMENT",
			'name'			=> "varchar(150) NOT NULL DEFAULT ''",
			'mycodekey'		=> "varchar(50) NOT NULL DEFAULT ''",
			'enabled'		=> "tinyint(1) NOT NULL DEFAULT '1'",
			'primary_key'	=> "cid",
			'unique_key' => ['mycodekey' => 'mycodekey'],
		],
		'ougc_mediainfo_categories_data'	=> [
			'did'			=> "int UNSIGNED NOT NULL AUTO_INCREMENT",
			'cid'			=> "int UNSIGNED NOT NULL DEFAULT '0'",
			'mid'			=> "int UNSIGNED NOT NULL DEFAULT '0'",
			'image'			=> "varchar(150) NOT NULL DEFAULT ''",
			'description'	=> "varchar(100) NOT NULL DEFAULT ''",
			'enabled'		=> "tinyint(1) NOT NULL DEFAULT '1'",
			'primary_key'	=> "did",
		],
	];
}

// List of columns
function _db_columns()
{
	return [
		'threads' => [
			'imdbid' => "varchar(15) NOT NULL DEFAULT ''"
		],
	];
}

// Verify DB indexes
function _db_verify_indexes()
{
	global $db;

	foreach(_db_tables() as $table => $fields)
	{
		if(!$db->table_exists($table))
		{
			continue;
		}

		if(isset($fields['unique_key']))
		{
			foreach($fields['unique_key'] as $k => $v)
			{
				if($db->index_exists($table, $k))
				{
					continue;
				}

				$db->write_query("ALTER TABLE {$db->table_prefix}{$table} ADD UNIQUE KEY {$k} ({$v})");
			}
		}
	}
}

// Verify DB tables
function _db_verify_tables()
{
	global $db;

	$collation = $db->build_create_table_collation();

	foreach(_db_tables() as $table => $fields)
	{
		if($db->table_exists($table))
		{
			foreach($fields as $field => $definition)
			{
				if($field == 'primary_key' || $field == 'unique_key')
				{
					continue;
				}

				if($db->field_exists($field, $table))
				{
					$db->modify_column($table, "`{$field}`", $definition);
				}
				else
				{
					$db->add_column($table, $field, $definition);
				}
			}
		}
		else
		{
			$query = "CREATE TABLE IF NOT EXISTS `{$db->table_prefix}{$table}` (";

			foreach($fields as $field => $definition)
			{
				if($field == 'primary_key')
				{
					$query .= "PRIMARY KEY (`{$definition}`)";
				}
				elseif($field != 'unique_key')
				{
					$query .= "`{$field}` {$definition},";
				}
			}

			$query .= ") ENGINE=MyISAM{$collation};";

			$db->write_query($query);
		}
	}

	_db_verify_indexes();
}

// Verify DB columns
function _db_verify_columns()
{
	global $db;

	foreach(_db_columns() as $table => $columns)
	{
		foreach($columns as $field => $definition)
		{
			if($db->field_exists($field, $table))
			{
				$db->modify_column($table, "`{$field}`", $definition);
			}
			else
			{
				$db->add_column($table, $field, $definition);
			}
		}
	}
}