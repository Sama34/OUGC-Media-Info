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

	function __construct()
	{
		global $plugins, $settings;

		// Tell MyBB when to run the hook
		if(!defined('IN_ADMINCP'))
		{
			$plugins->add_hook('newthread_end', array($this, 'hook_newthread_end'));

			//$plugins->add_hook('datahandler_post_validate_post', array($this, 'hook_datahandler_post_validate_post'));
			$plugins->add_hook('datahandler_post_validate_thread', array($this, 'hook_datahandler_post_validate_post'));
	
			$plugins->add_hook('datahandler_post_insert_thread', array($this, 'hook_datahandler_post_insert_thread'));
	
			$plugins->add_hook('showthread_end', array($this, 'hook_showthread_end'));
		}

		//$this->key = (string)$settings['ougc_mediainfo_key'];
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
		));

		// Insert template/group
		$PL->templates('ougcmediainfo', 'OUGC Media Info', array(
			''	=> '',
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
			''	=> '',
			''	=> ''
		));

		require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

		find_replace_templatesets('newthread', '#'.preg_quote('{$posticons}').'#i', '{$ougc_mediainfo_input}{$posticons}');
		find_replace_templatesets('editpost', '#'.preg_quote('{$posticons}').'#i', '{$ougc_mediainfo_input}{$posticons}');

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
				'writer'		=> "varchar(1000) NOT NULL DEFAULT ''",
				'actors'		=> "varchar(250) NOT NULL DEFAULT ''",
				'plot'			=> "varchar(500) NOT NULL DEFAULT ''",
				'language'		=> "varchar(100) NOT NULL DEFAULT ''",
				'country'		=> "varchar(50) NOT NULL DEFAULT ''",
				'awards'		=> "varchar(150) NOT NULL DEFAULT ''",
				'poster'		=> "varchar(150) NOT NULL DEFAULT ''",
				'ratings'		=> "varchar(500) NOT NULL DEFAULT ''",
				'metascore'		=> "tinyint(5) NOT NULL DEFAULT '0'",
				'imdbrating'	=> "float(4,2) UNSIGNED NOT NULL DEFAULT '0.00'",
				'imdbvotes'		=> "int(10) NOT NULL DEFAULT '0'",
				'imdbid'		=> "varchar(15) NOT NULL DEFAULT ''",
				'type'			=> "varchar(15) NOT NULL DEFAULT ''",
				'dvd'			=> "int(10) NOT NULL DEFAULT '0'",
				'boxoffice'		=> "varchar(15) NOT NULL DEFAULT ''",
				'production'	=> "varchar(50) NOT NULL DEFAULT ''",
				'website'		=> "varchar(150) NOT NULL DEFAULT ''",
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
		global $mybb, $fid, $templates, $ougc_mediainfo_input, $lang;

		if(!is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $fid)))
		{
			return;
		}
	
		$this->load_language();

		$imdbid = '';
		if($mybb->request_method == 'post')
		{
			$imdbid = htmlspecialchars_uni($mybb->get_input('imdbid'));
		}

		$ougc_mediainfo_input = eval($templates->render('ougcmediainfo_input'));
	}

	// Hook: 
	function hook_()
	{
	}

	function hook_datahandler_post_validate_post(&$dh)
	{
		global $mybb, $lang, $db;

		if(!is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $dh->data['fid'])))
		{
			return;
		}
	
		$this->load_language();

		preg_match('#^https://(?:www\.)?imdb\.com/title/(tt[^/]+/)$#', $mybb->get_input('imdbid'), $match);

		if(!$match || empty($match[1]))
		{
			$dh->set_error($lang->ougc_mediainfo_error_nomatch);

			return;
		}

		$imdbid = str_replace('/', '', $match[1]);

		$dh->ougc_mediainfo = $this->get_media($imdbid);

		if(empty($dh->ougc_mediainfo) || (string)$dh->ougc_mediainfo['Response'] != 'True')
		{
			$dh->set_error($lang->ougc_mediainfo_error_apikey);

			return;
		}

		$dh->data['imdbid'] = $imdbid;
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

		$this->inset_data($dh->data['imdbid'], $dh->ougc_mediainfo);
	}

	// Hook: 
	function hook_showthread_end()
	{
		global $mybb, $thread, $db;

		if(empty($thread['imdbid']) || !is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $thread['fid'])))
		{
			return;
		}

		$imdbid = $db->escape_string($thread['imdbid']);

		$query = $db->simple_select('ougc_mediainfo', '*', "imdbid='{$imdbid}'");

		$imdb = $db->fetch_array($query);

		if(empty($imdb['mid']))
		{
			return;
		}

		_dump($imdb);
	}

	function get_media($imdbid)
	{
		global $mybb;

		$json = file_get_contents("http://www.omdbapi.com/?i={$imdbid}&apikey={$mybb->settings['ougc_mediainfo_apikey']}");

		return json_decode($json, true);
	}

	function inset_data($imdbid, $data)
	{
		global $mybb, $db;

		$imdbid = $db->escape_string($imdbid);

		$query = $db->simple_select('ougc_mediainfo', '*', "imdbid='{$imdbid}'");
		$update = $db->num_rows($query);

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
			'dvd'		=> strtotime($data['DVD']),
			'boxoffice'	=> $db->escape_string($data['BoxOffice']),
			'production'=> $db->escape_string($data['Production']),
			'website'	=> $db->escape_string($data['Website'])
		);

		foreach($insert_data as $key => &$value)
		{
			if($value == 'N/A')
			{
				$value = '';
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

		$ext = get_extension(my_strtolower($data['Poster']));

		if(!is_writable($images_path) || !in_array($ext, array('gif', 'png', 'jpg', 'jpeg', 'jpe')))
		{
			return;
		}

		$image = file_get_contents($data['Poster']);

		$fp = @fopen("{$images_path}/{$imdbid}.{$ext}", 'w');

		if($fp)
		{
			@fwrite($fp, $image);
		}

		@fclose($fp);
	}
}

global $ougc_mediainfo;

$ougc_mediainfo = new OUGC_MediaInfo;

//my_date($mybb->settings['dateformat'], $insert_data['dvd'])
//my_date($mybb->settings['dateformat'], $insert_data['released'])