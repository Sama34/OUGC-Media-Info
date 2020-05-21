<?php

/***************************************************************************
 *
 *	OUGC Media Info plugin (/inc/plugins/ougc_mediainfo.php)
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

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Plugin API
function ougc_mediainfo_info()
{
	global $ougc_mediainfo;

	return $ougc_mediainfo->_info();
}

// _activate() routine
function ougc_mediainfo_activate()
{
	global $ougc_mediainfo;

	return $ougc_mediainfo->_activate();
}

// _deactivate() routine
function ougc_mediainfo_deactivate()
{
	global $ougc_mediainfo;

	return $ougc_mediainfo->_deactivate();
}

// _install() routine
function ougc_mediainfo_install()
{
	global $ougc_mediainfo;

	return $ougc_mediainfo->_install();
}

// _is_installed() routine
function ougc_mediainfo_is_installed()
{
	global $ougc_mediainfo;

	return $ougc_mediainfo->_is_installed();
}

// _uninstall() routine
function ougc_mediainfo_uninstall()
{
	global $ougc_mediainfo;

	return $ougc_mediainfo->_uninstall();
}

// Plugin class
class OUGC_MediaInfo
{
	public $key = null;

	public $force_update = false;

	public $remove_image = '';

	function __construct()
	{
		global $plugins, $settings, $templatelist;

		// Tell MyBB when to run the hook
		if(!defined('IN_ADMINCP'))
		{
			$plugins->add_hook('newthread_end', array($this, 'hook_newthread_end'));
			$plugins->add_hook('editpost_end', array($this, 'hook_newthread_end'));

			$plugins->add_hook('datahandler_post_validate_post', array($this, 'hook_datahandler_post_validate_post'));
			$plugins->add_hook('datahandler_post_validate_thread', array($this, 'hook_datahandler_post_validate_post'));
	
			$plugins->add_hook('datahandler_post_insert_thread', array($this, 'hook_datahandler_post_insert_thread'));
			$plugins->add_hook('datahandler_post_update_thread', array($this, 'hook_datahandler_post_update_thread'));
	
			$plugins->add_hook('showthread_end', array($this, 'hook_showthread_end'));
	
			$plugins->add_hook('forumdisplay_thread_end', array($this, 'hook_forumdisplay_thread_end'));
	
			$plugins->add_hook('class_moderation_delete_thread_start', array($this, 'hook_class_moderation_delete_thread_start'));
			$plugins->add_hook('class_moderation_merge_threads', array($this, 'hook_class_moderation_merge_threads'));

			$plugins->add_hook('search_end', array($this, 'hook_search_end'));
			$plugins->add_hook('search_do_search_start', array($this, 'hook_search_do_search_start'));

			if(isset($templatelist))
			{
				$templatelist .= ',';
			}
			else
			{
				$templatelist = '';
			}

			if(defined('THIS_SCRIPT'))
			{
				if(THIS_SCRIPT == 'forumdisplay.php' || THIS_SCRIPT == 'showthread.php')
				{
					$templatelist .= ',ougcmediainfo_field, ougcmediainfo_rating, ougcmediainfo';
				}
				
				if(THIS_SCRIPT == 'forumdisplay.php')
				{
					$templatelist .= ',ougcmediainfo_popup, ougcmediainfo_id, ougcmediainfo_js';
				}

				if(THIS_SCRIPT == 'search.php')
				{
					$templatelist .= ',ougcmediainfo_search';
				}
			}
		}
	}

	// Plugin API:_info() routine
	function _info()
	{
		global $lang;

		$this->load_language();

		return array(
			'name'			=> 'OUGC Media Info',
			'description'	=> $lang->setting_group_ougc_mediainfo_desc,
			'website'		=> 'https://ougc.network',
			'author'		=> 'Omar G.',
			'authorsite'	=> 'https://ougc.network',
			'version'		=> '1.8.0',
			'versioncode'	=> 1800,
			'compatibility'	=> '18*',
			'codename'		=> 'ougc_mediainfo',
			'pl'			=> array(
				'version'	=> 13,
				'url'		=> 'https://community.mybb.com/mods.php?action=view&pid=573'
			)
		);
	}

	// Plugin API:_activate() routine
	function _activate()
	{
		global $PL, $lang, $mybb;

		$this->load_pluginlibrary();

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
		));

		// Insert template/group
		$PL->templates('ougcmediainfo', 'OUGC Media Info', array(
			''	=> '<tr>
	<td class="trow1">
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}">
			<tr>
				<td align="center" width="20%">
					<img src="{$image_source}" alt="{$media[\'title\']}" title="{$media[\'title\']}" style="max-width: 300px;max-height: 300px;" />
				</td>
				<td width="80%">
					{$type}
					{$title}
					{$year}
					{$released}
					{$runtime}
					{$genre}
					{$director}
					{$writer}
					{$actors}
					{$plot}
					{$language}
					{$country}
					{$awards}
					{$production}
					{$metascore}
					{$imdbvotes}
					{$rated}
					{$rating_list}
				</td>
			</tr>
		</table>
	</td>
</tr>',
			'field'	=> '<div><strong>{$name}</strong> {$value}</div>',
			'input'	=> '<tr>
	<td class="trow2" width="20%">
		<strong>{$lang->ougc_mediainfo_input}</strong>
	</td>
	<td class="trow2">
		<input type="text" class="textbox" name="imdbid" size="40" maxlength="85" value="{$imdbid}" tabindex="2" placeholder="{$lang->ougc_mediainfo_input_placeholder}" />
		<div class="smalltext">
			{$lang->ougc_mediainfo_input_desc}
		</div>
	</td>
</tr>',
			'rating'	=> '{$name}: <i>{$value}</i><br />',
			'id'	=> ' id="ougcmediainfo_{$thread[\'tid\']}"',
			'js'	=> '<script>
	$(function() {
		var moveLeft = 20;
		var moveDown = 10;

		$("[id^=ougcmediainfo_]").hover(function(e) {
			id = $(this).attr(\'id\');
			tid = id.replace( /[^\d.]/g, \'\');

			$(\'#ougcmediainfo_popup_\' + tid).fadeIn(50)
			.css(\'top\', e.pageY + moveDown)
			.css(\'left\', e.pageX + moveLeft);
			//.appendTo(\'body\');
			}, function() {
				$(\'#ougcmediainfo_popup_\' + tid).hide();
			});

			$(\'#ougcmediainfo_\' + tid).mousemove(function(e) {
			$(\'#ougcmediainfo_popup_\' + tid).css(\'top\', e.pageY + moveDown).css(\'left\', e.pageX + moveLeft);
		});
	});
	//https://codepen.io/thebalu/pen/NqErJO
</script>
<style>
	*[id*=\'ougcmediainfo_popup_\'] {
		display: none;
		position: absolute;
		max-width: 25%;
		left: 20%;
	}

	*[id*=\'ougcmediainfo_popup_\'], *[id*=\'ougcmediainfo_popup_\'] * {
		font-size: 98%;
	}

	*[id*=\'ougcmediainfo_popup_\'] img {
		max-width: 100px !important;
		max-height: 100px !important;
	}
</style>',
			'popup'	=> '<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder clear" id="ougcmediainfo_popup_{$thread[\'tid\']}">
	<tr>
		<td class="thead">
			<strong>{$media[\'title\']}</strong>
		</td>
	</tr>
	{$ougc_mediainfo_display}
</table>',
			'search'	=> '<br /><input type="radio" class="radio" name="postthread" value="imdbid" />{$lang->ougc_mediainfo_search}'
		));

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

		$this->load_plugin_info();

		if(!isset($plugins['mediainfo']))
		{
			$plugins['mediainfo'] = $this->plugin_info['versioncode'];
		}

		/*~*~* RUN UPDATES START *~*~*/
		$this->_db_verify_tables();
		$this->_db_verify_columns();
		$this->_db_verify_indexes();

		/*~*~* RUN UPDATES END *~*~*/

		$plugins['mediainfo'] = $this->plugin_info['versioncode'];

		$mybb->cache->update('ougc_plugins', $plugins);
	}

	// Plugin API:_deactivate() routine
	function _deactivate()
	{
		// Revert template edits
		require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

		find_replace_templatesets('newthread', '#'.preg_quote('{$ougc_mediainfo_input}').'#i', '', 0);
		find_replace_templatesets('editpost', '#'.preg_quote('{$ougc_mediainfo_input}').'#i', '', 0);
		find_replace_templatesets('showthread', '#'.preg_quote('{$ougc_mediainfo_display}').'#i', '', 0);
		find_replace_templatesets('forumdisplay_thread', '#'.preg_quote('{$ougc_mediainfo_id}').'#i', '', 0);
		find_replace_templatesets('forumdisplay', '#'.preg_quote('{$ougc_mediainfo_forumdisplay_js}').'#i', '', 0);
		find_replace_templatesets('forumdisplay_thread', '#'.preg_quote('{$ougc_mediainfo_popup}').'#i', '', 0);
		find_replace_templatesets('search', '#'.preg_quote('{$ougc_mediainfo_search}').'#i', '', 0);
	}

	// Plugin API:_install() routine
	function _install()
	{
		$this->_db_verify_tables();
		$this->_db_verify_columns();
		$this->_db_verify_indexes();
	}

	// Plugin API:_is_installed() routine
	function _is_installed()
	{
		global $db;
	
		foreach($this->_db_tables() as $name => $table)
		{
			$installed = $db->table_exists($name);

			break;
		}
	
		return $installed;
	}

	// Plugin API:_uninstall() routine
	function _uninstall()
	{
		global $PL, $cache, $db;

		$this->load_pluginlibrary();

		// Drop DB entries
		foreach($this->_db_tables() as $name => $table)
		{
			$db->drop_table($name);
		}

		foreach($this->_db_columns() as $table => $columns)
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
	}

	// Load language file
	function load_language()
	{
		global $lang;

		isset($lang->setting_group_ougc_mediainfo) or $lang->load('ougc_mediainfo');
	}

	// Build plugin info
	function load_plugin_info()
	{
		$this->plugin_info = ougc_mediainfo_info();
	}

	// PluginLibrary requirement check
	function load_pluginlibrary()
	{
		global $lang;

		$this->load_plugin_info();
	
		$this->load_language();

		if($file_exists = file_exists(PLUGINLIBRARY))
		{
			global $PL;

			$PL or require_once PLUGINLIBRARY;
		}

		if(!$file_exists || $PL->version < $this->plugin_info['pl']['version'])
		{
			flash_message($lang->sprintf($lang->ougc_mediainfo_pluginlibrary, $this->plugin_info['pl']['ulr'], $this->plugin_info['pl']['version']), 'error');
			admin_redirect('index.php?module=config-plugins');
		}
	}

	// List of tables
	function _db_tables()
	{
		global $db;

		$collation = $db->build_create_table_collation();
	
		$tables = array(
			'ougc_mediainfo'	=> array(
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
				'language'		=> "varchar(100) NOT NULL DEFAULT ''",
				'country'		=> "varchar(50) NOT NULL DEFAULT ''",
				'awards'		=> "varchar(150) NOT NULL DEFAULT ''",
				'poster'		=> "varchar(200) NOT NULL DEFAULT ''",
				'ratings'		=> "text NULL",
				'metascore'		=> "tinyint(5) NOT NULL DEFAULT '0'",
				'imdbrating'	=> "float(4,2) UNSIGNED NOT NULL DEFAULT '0.00'",
				'imdbvotes'		=> "int(10) NOT NULL DEFAULT '0'",
				'imdbid'		=> "varchar(15) NOT NULL DEFAULT ''",
				'type'			=> "varchar(15) NOT NULL DEFAULT ''",
				'production'	=> "varchar(50) NOT NULL DEFAULT ''",
				'image'		=> "varchar(150) NOT NULL DEFAULT ''",
				'prymary_key'	=> "mid"
			)
		);

		return $tables;
	}

	// List of columns
	function _db_columns()
	{
		$tables = array(
			'threads'	=> array(
				'imdbid' => "varchar(15) NOT NULL DEFAULT ''"
			),
		);

		return $tables;
	}

	// Verify DB indexes
	function _db_verify_indexes()
	{
		global $db;

		if(!$db->index_exists('ougc_mediainfo', 'imdbid'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."ougc_mediainfo ADD UNIQUE KEY `imdbid` (`imdbid`)");
		}
	}

	// Verify DB tables
	function _db_verify_tables()
	{
		global $db;

		$collation = $db->build_create_table_collation();
		foreach($this->_db_tables() as $table => $fields)
		{
			if($db->table_exists($table))
			{
				foreach($fields as $field => $definition)
				{
					if($field == 'prymary_key')
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
				$query = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."{$table}` (";
				foreach($fields as $field => $definition)
				{
					if($field == 'prymary_key')
					{
						$query .= "PRIMARY KEY (`{$definition}`)";
					}
					else
					{
						$query .= "`{$field}` {$definition},";
					}
				}
				$query .= ") ENGINE=MyISAM{$collation};";
				$db->write_query($query);
			}
		}
	}

	// Verify DB columns
	function _db_verify_columns()
	{
		global $db;

		foreach($this->_db_columns() as $table => $columns)
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

	// Hook: newthread_end
	function hook_newthread_end()
	{
		global $mybb, $fid, $templates, $ougc_mediainfo_input, $lang, $thread, $pid;

		if(!is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $fid)))
		{
			return;
		}

		if($plugins->current_hook == 'editpost_end' && $pid != $thread['firstpost'])
		{
			return;
		}

		$this->load_language();

		$imdbid = '';

		if($mybb->request_method == 'post')
		{
			$imdbid = htmlspecialchars_uni($mybb->get_input('imdbid'));
		}
		elseif(isset($thread['imdbid']) && !empty($thread['imdbid']))
		{
			$imdbid = htmlspecialchars_uni("https://www.imdb.com/title/{$thread['imdbid']}/");
		}

		$ougc_mediainfo_input = eval($templates->render('ougcmediainfo_input'));
	}

	// Hook: 
	function hook_()
	{
	}

	function hook_datahandler_post_validate_post(&$dh)
	{
		global $mybb, $lang, $db, $plugins, $thread;

		if(!is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $dh->data['fid'])))
		{
			return;
		}

		if($plugins->current_hook == 'datahandler_post_validate_post' && !$dh->first_post)
		{
			return;
		}

		$this->load_language();

		$imdbid = $this->get_imdbid($mybb->get_input('imdbid', MyBB::INPUT_STRING));

		if(empty($imdbid))
		{
			$dh->set_error($lang->ougc_mediainfo_error_nomatch);

			return;
		}

		$dh->ougc_mediainfo = $this->get_media($imdbid);

		if(empty($dh->ougc_mediainfo))
		{
			$dh->set_error($lang->ougc_mediainfo_error_apikey);

			return;
		}

		$dh->data['imdbid'] = $imdbid;

		if($plugins->current_hook == 'datahandler_post_validate_post')
		{
			$dh->thread_update_data['imdbid'] = $imdbid;

			$this->set_remove_media($thread['tid'], $thread['imdbid']);
		}
	}

	function set_remove_media($tid, $imdbid)
	{
		$this->remove_image = array('tid' => (int)$tid, 'imdbid' => $imdbid);
	}

	// Hook: 
	function hook_datahandler_post_insert_thread(&$dh)
	{
		if(empty($dh->data['imdbid']))
		{
			return;
		}

		global $db;

		$dh->thread_insert_data['imdbid'] = $db->escape_string($dh->data['imdbid']);

		$this->insert_data($dh->data['imdbid'], $dh->ougc_mediainfo);
	}

	// Hook: 
	function hook_datahandler_post_update_thread(&$dh)
	{
		if(empty($dh->thread_update_data['imdbid']))
		{
			return;
		}

		global $db;

		$dh->thread_update_data['imdbid'] = $db->escape_string($dh->thread_update_data['imdbid']);

		$this->insert_data($dh->thread_update_data['imdbid'], $dh->ougc_mediainfo);
	}

	// Hook: 
	function hook_showthread_end()
	{
		global $mybb, $thread, $db, $ougc_mediainfo_display, $templates, $lang;

		$ougc_mediainfo_display = '';

		if(empty($thread['imdbid']) || !is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $thread['fid'])))
		{
			return;
		}

		$imdbid = $db->escape_string($thread['imdbid']);

		$query = $db->simple_select('ougc_mediainfo', '*', "imdbid='{$imdbid}'");

		$media = $db->fetch_array($query);

		$ougc_mediainfo_display = $this->render($media);
	}

	// Hook: 
	function hook_forumdisplay_thread_end()
	{
		global $mybb, $thread, $db, $ougc_mediainfo_id, $templates, $lang, $threadcache, $theme, $ougc_mediainfo_popup, $plugins;

		$ougc_mediainfo_id = $ougc_mediainfo_popup = '';

		if(empty($thread['imdbid']) || !is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $thread['fid'])))
		{
			return;
		}

		static $media_cache = null;

		if($media_cache === null)
		{
			$media_cache = $imdbids = array();

			foreach($threadcache as $_thread)
			{
				if(!empty($_thread['imdbid']))
				{
					$imdbids[$_thread['imdbid']] = $db->escape_string($_thread['imdbid']);
				}
			}

			$imdbids = implode("','", $imdbids);

			$query = $db->simple_select('ougc_mediainfo', '*', "imdbid IN ('{$imdbids}')");

			while($media = $db->fetch_array($query))
			{
				$media_cache[$media['imdbid']] = $media;
			}
		}

		$media = $media_cache[$thread['imdbid']];

		$media['title'] = htmlspecialchars_uni($media['title']);

		$ougc_mediainfo_display = $this->render($media);

		$ougc_mediainfo_popup = eval($templates->render('ougcmediainfo_popup'));

		$ougc_mediainfo_id = eval($templates->render('ougcmediainfo_id', true, false));

		$plugins->add_hook('forumdisplay_threadlist', array($this, 'hook_forumdisplay_threadlist'));
	}

	function hook_class_moderation_delete_thread_start(&$tid)
	{
		global $plugins, $db;

		$tid = (int)$tid;

		$thread = get_thread($tid);

		$imdbid = $db->escape_string($thread['imdbid']);

		$query = $db->simple_select('threads', 'tid', "tid!='{$tid}' AND imdbid='{$imdbid}'");

		$existing_threads = $db->num_rows($query);

		if(!$existing_threads)
		{
			$this->set_remove_media($thread['tid'], $thread['imdbid']);
		
			$plugins->add_hook('class_moderation_delete_thread', array($this, 'hook_class_moderation_delete_thread'));
		}
	}

	function hook_class_moderation_delete_thread(&$tid)
	{
		$this->remove_media();
	}

	function hook_class_moderation_merge_threads(&$args)
	{
		global $db;

		$old_thread = get_thread($args['mergetid']);

		$imdbid = $db->escape_string($old_thread['imdbid']);

		$tid = (int)$args['tid'];

		$db->update_query("threads", array('imdbid' => $imdbid), "tid = '{$tid}'");
	}

	function hook_forumdisplay_threadlist()
	{
		global $ougc_mediainfo_forumdisplay_js, $templates;

		$ougc_mediainfo_forumdisplay_js = eval($templates->render('ougcmediainfo_js'));
	}

	function hook_search_end()
	{
		global $mybb, $ougc_mediainfo_search, $templates, $lang;

		if(!$mybb->settings['ougc_mediainfo_enablesearch'])
		{
			return;
		}

		$this->load_language();

		$ougc_mediainfo_search = eval($templates->render('ougcmediainfo_search', true, false));
	}

	function hook_search_do_search_start()
	{
		global $mybb, $db;

		$full_search = $mybb->get_input('postthread', MyBB::INPUT_STRING) === 'imdbid';

		$forum_display = count($mybb->input) < 10 && $mybb->get_input('postthread', MyBB::INPUT_INT) && !$full_search;

		if(!$full_search && !$forum_display || !$mybb->settings['ougc_mediainfo_enablesearch'])
		{
			return;
		}

		if($mybb->input['forums'][0] != 'all')
		{
			if(is_array($mybb->input['forums']))
			{
				$valid_forum = false;

				foreach($mybb->input['forums'] as $fid)
				{
					if(is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $fid)))
					{
						$valid_forum = true;

						break;
					}
				}

				if(!$valid_forum)
				{
					return;
				}
			}
			elseif(!is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $mybb->get_input('forums', MyBB::INPUT_INT))))
			{
				return;
			}
		}

		if(!($imdbid = $this->get_imdbid($mybb->get_input('keywords', MyBB::INPUT_STRING))))
		{
			return;
		}

		$mybb->input['postthread'] = 1;

		$mybb->input['keywords'] = $imdbid;

		control_object($db, '
			function query($string, $hide_errors=0, $write_query=0)
			{
				static $done = false;

				if(!$done && !$write_query && my_strpos($string, \'SELECT p.pid, p.tid\') !== false && my_strpos($string, \'p.message LIKE\') !== false)
				{
					$done = true;

					$string = strtr($string, array(
						\'p.message LIKE\' => \'t.imdbid LIKE\'
					));
				}

				return parent::query($string, $hide_errors, $write_query);
			}
		');
	}

	function render($media)
	{
		global $templates, $lang, $mybb;

		if(empty($media['mid']))
		{
			return;
		}
	
		$this->load_language();

		if(THIS_SCRIPT == 'showthread.php')
		{
			$disettings = explode(',', $mybb->settings['ougc_mediainfo_fields_thread']);
		}
		else
		{
			$disettings = explode(',', $mybb->settings['ougc_mediainfo_fields_forumlist']);
		}

		$disettings = array_flip($disettings);

		foreach(array('mid', 'year', 'metascore', 'imdbvotes', 'released') as $field)
		{
			$media[$field] = (int)$media[$field];

			if($field == 'mid' || empty($media[$field]))
			{
				continue;
			}

			if($field == 'imdbvotes')
			{
				$media[$field] = my_number_format($media[$field]);
			}

			if($field == 'released')
			{
				$media[$field] = my_date($mybb->settings['dateformat'], $media[$field]);
			}

			if(!isset($disettings[$field]))
			{
				continue;
			}

			$name = $lang->{'ougc_mediainfo_field_'.$field};
			$value = $media[$field];

			${$field} = eval($templates->render('ougcmediainfo_field'));
		}

		foreach(array('title', 'rated', 'runtime', 'genre', 'director', 'writer', 'actors', 'plot', 'language', 'country', 'awards', 'poster', 'imdbid', 'type', 'production', 'image', 'imdbrating') as $field)
		{
			$media[$field] = htmlspecialchars_uni($media[$field]);

			if($field == 'poster' || $field == 'image' || empty($media[$field]))
			{
				continue;
			}

			if($field == 'imdbrating')
			{
				$media[$field] = (float)$media[$field];
			}
	
			if($field == 'type')
			{
				$media[$field] = ucfirst($media[$field]);
			}

			if(!isset($disettings[$field]))
			{
				continue;
			}

			$name = $lang->{'ougc_mediainfo_field_'.$field};
			$value = $media[$field];

			${$field} = eval($templates->render('ougcmediainfo_field'));
		}

		$imdb_url = htmlspecialchars_uni("https://www.imdb.com/title/{$media['imdbid']}/");

		$ratings = my_unserialize($media['ratings']);

		$rating_list = '';

		if(isset($disettings['rating_list']))
		{
			foreach((array)$ratings as &$rating)
			{
				if(empty($rating['Source']) || empty($rating['Value']))
				{
					continue;
				}

				$name = $rating['Source'] = htmlspecialchars_uni($rating['Source']);
				$value = $rating['Value'] = htmlspecialchars_uni($rating['Value']);
	
				$rating_list = eval($templates->render('ougcmediainfo_rating'));
			}
		}

		if($rating_list)
		{
			$name = $lang->ougc_mediainfo_field_ratings;
			$value = $rating_list;

			$rating_list = eval($templates->render('ougcmediainfo_field'));
		}

		if($media['image'])
		{
			if($mybb->settings['usecdn'] && !empty($mybb->settings['cdnurl']))
			{
				$image_source = $mybb->settings['cdnurl'].'/uploads/ougc_mediainfo/'.$media['image'];
			}
			else
			{
				$image_source = $mybb->settings['bburl'].'/uploads/ougc_mediainfo/'.$media['image'];
			}
		}
		else
		{
			$image_source = $media['poster'];
		}

		return eval($templates->render('ougcmediainfo'));
	}

	function get_media($imdbid)
	{
		global $mybb;

		$json = file_get_contents("http://www.omdbapi.com/?i={$imdbid}&apikey={$mybb->settings['ougc_mediainfo_apikey']}");

		$omdb_data = json_decode($json, true);

		if(empty($omdb_data['Error']) && $omdb_data['Response'] != 'False')
		{
			foreach($omdb_data as $key => $value)
			{
				if(my_strtolower((string)$value) == 'n/a')
				{
					$omdb_data[$key] = '';
				}
			}
		}
		else
		{
			$omdb_data = false;
		}

		// We get some alternative data because the OMDB data might be incomplete
		include_once MYBB_ROOT.'inc/plugins/ougc_mediainfo/imdb.class.php';

		$imdb = new IMDB($imdbid);

		$imdb = $imdb->getAll();

		$imdb_data = array(
			'Title'		=> $imdb['getTitle']['value'],
			'Year'		=> $imdb['getYear']['value'],
			'Rated'		=> $imdb['getMpaa']['value'],
			'Released'	=> $imdb['getReleaseDate']['value'],
			'Runtime'	=> $imdb['getRuntime']['value'],
			'Genre'		=> str_replace(' /', ',', $imdb['getGenre']['value']),
			'Director'	=> str_replace(' /', ',', $imdb['getDirector']['value']),
			'Writer'	=> str_replace(' /', ',', $imdb['getWriter']['value']),
			'Actors'	=> str_replace(' /', ',', $imdb['getCast']['value']),
			'Plot'		=> str_replace(' /', ',', $imdb['getDescription']['value']),
			'Language'	=> str_replace(' /', ',', $imdb['getLanguage']['value']),
			'Country'	=> $imdb['getLocation']['value'],
			'Awards'	=> str_replace(' /', ',', $imdb['getAwards']['value']),
			'Poster'	=> $imdb['getPoster']['value'],
			'Ratings'	=> '',
			'Metascore'	=> '',
			'imdbRating'=> $imdb['getRating']['value'],
			'imdbVotes'	=> $imdb['getRatingCount']['value'],
			'imdbID'	=> $imdbid,
			'Type'	=> '',
			'Production'	=> $imdb['getCompany']['value'],
		);

		foreach($imdb_data as $key => $value)
		{
			if(my_strtolower((string)$value) == 'n/a')
			{
				$value = '';
			}

			if(empty($omdb_data[$key]) && !empty($value))
			{
				$omdb_data[$key] = $value;
			}
		}

		if(empty($omdb_data['Title']))
		{
			return false;
		}

		return $omdb_data;
	}

	function insert_data($imdbid, $data)
	{
		global $mybb, $db;

		$imdbid = $db->escape_string($imdbid);

		$query = $db->simple_select('ougc_mediainfo', '*', "imdbid='{$imdbid}'");
		$update = (bool)$db->num_rows($query);

		$insert_data = array(
			'title'		=> $db->escape_string($data['Title']),
			'year'		=> (int)$data['Year'],
			'rated'		=> $db->escape_string($data['Rated']),
			'released'	=> strtotime($data['Released']),
			'runtime'	=> $db->escape_string($data['Runtime']),
			'genre'		=> $db->escape_string($data['Genre']),
			'director'	=> $db->escape_string($data['Director']),
			'writer'	=> $db->escape_string($data['Writer']),
			'actors'	=> $db->escape_string($data['Actors']),
			'plot'		=> $db->escape_string($data['Plot']),
			'language'	=> $db->escape_string($data['Language']),
			'country'	=> $db->escape_string($data['Country']),
			'awards'	=> $db->escape_string($data['Awards']),
			'poster'	=> $db->escape_string($data['Poster']),
			'ratings'	=> $db->escape_string(my_serialize($data['Ratings'])),
			'metascore'	=> (int)$data['Metascore'],
			'imdbrating'=> (float)$data['imdbRating'],
			'imdbvotes'	=> (int)str_replace(',', '', (string)$data['imdbVotes']),
			'type'		=> $db->escape_string($data['Type']),
			'production'=> $db->escape_string($data['Production'])
		);

		foreach($insert_data as $key => &$value)
		{
			if(my_strtolower((string)$value) == 'n/a')
			{
				$value = '';
			}

			if(empty($value) && !$this->force_update)
			{
				unset($insert_data[$key]);
				// so no data is deleted on update
			}
		}

		if($update)
		{
			$db->update_query('ougc_mediainfo', $insert_data, "imdbid='{$imdbid}'");
		}
		else
		{
			$insert_data['imdbid'] = $imdbid;

			$db->insert_query('ougc_mediainfo', $insert_data);
		}

		$images_path = MYBB_ROOT.'uploads/ougc_mediainfo';

		$ext = get_extension(my_strtolower((string)$data['Poster']));

		if(!is_writable($images_path) || !in_array($ext, array('gif', 'png', 'jpg', 'jpeg', 'jpe')) || !function_exists('curl_init'))
		{
			return;
		}

		if(!($image = file_get_contents($data['Poster'])) || !($headers = get_headers($data['Poster'], 1)))
		{
			return;
		}

		switch(my_strtolower((string)$headers['Content-Type']))
		{
			case "image/gif":
			case "image/jpeg":
			case "image/x-jpg":
			case "image/x-jpeg":
			case "image/pjpeg":
			case "image/jpg":
			case "image/png":
			case "image/x-png":
				$valid_image = true;
				break;
			default:
				$valid_image = false;
				break;
		}

		if(!$headers['Content-Length'] || !$valid_image)
		{
			return;
		}

		$fp = @fopen("{$images_path}/{$imdbid}.{$ext}", 'w');

		if($fp)
		{
			@fwrite($fp, $image);

			$db->update_query('ougc_mediainfo', array('image' => $db->escape_string("{$imdbid}.{$ext}")), "imdbid='{$imdbid}'");
		}

		@fclose($fp);

		//$cdn_path = '';

		//copy_file_to_cdn("{$images_path}/{$imdbid}.{$ext}", $cdn_path);

		//require_once MYBB_ROOT.'inc/functions_upload.php';

		//upload_file($data['Poster'], MYBB_ROOT.'uploads/ougc_mediainfo', "{$imdbid}.{$ext}");

		$this->remove_media($imdbid);
	}

	function remove_media($new_image='')
	{
		global $db;

		if(!empty($this->remove_image) && $this->remove_image['imdbid'] != $new_image)
		{
			$imdbid = $db->escape_string($this->remove_image['imdbid']);

			$query = $db->simple_select('threads', 'tid', "tid!='{$this->remove_image['tid']}' AND imdbid='{$imdbid}'");
			$existing = $db->num_rows($query);

			if(!$existing)
			{
				$query = $db->simple_select('ougc_mediainfo', '*', "imdbid='{$imdbid}'");
				$media = $db->fetch_array($query);

				require_once MYBB_ROOT.'inc/functions_upload.php';

				delete_uploaded_file(MYBB_ROOT.'uploads/ougc_mediainfo/'.$media['image']);

				$db->delete_query('ougc_mediainfo', "imdbid='{$imdbid}'");
			}
		}
	}

	function get_imdbid($string)
	{
		preg_match('#^https://(?:www\.)?imdb\.com/title/(tt\\d{7,8})/$#', $string, $match);

		if(empty($match[1]))
		{
			preg_match('/tt\\d{7,8}/', $string, $match);

			$imdbid = $match[0];
		}
		else
		{
			$imdbid = $match[1];
		}

		if(empty($imdbid))
		{
			return false;
		}

		return (string)$imdbid;
	}
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com ), 1.62
if(!function_exists('control_object'))
{
	function control_object(&$obj, $code)
	{
		static $cnt = 0;
		$newname = '_objcont_'.(++$cnt);
		$objserial = serialize($obj);
		$classname = get_class($obj);
		$checkstr = 'O:'.strlen($classname).':"'.$classname.'":';
		$checkstr_len = strlen($checkstr);
		if(substr($objserial, 0, $checkstr_len) == $checkstr)
		{
			$vars = array();
			// grab resources/object etc, stripping scope info from keys
			foreach((array)$obj as $k => $v)
			{
				if($p = strrpos($k, "\0"))
				{
					$k = substr($k, $p+1);
				}
				$vars[$k] = $v;
			}
			if(!empty($vars))
			{
				$code .= '
					function ___setvars(&$a) {
						foreach($a as $k => &$v)
							$this->$k = $v;
					}
				';
			}
			eval('class '.$newname.' extends '.$classname.' {'.$code.'}');
			$obj = unserialize('O:'.strlen($newname).':"'.$newname.'":'.substr($objserial, $checkstr_len));
			if(!empty($vars))
			{
				$obj->___setvars($vars);
			}
		}
		// else not a valid object or PHP serialize has changed
	}
}

global $ougc_mediainfo;

$ougc_mediainfo = new OUGC_MediaInfo;