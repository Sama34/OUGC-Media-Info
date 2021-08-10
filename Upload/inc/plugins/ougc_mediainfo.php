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

define('OUGC_MEDIAINFO_ROOT', MYBB_ROOT . 'inc/plugins/ougc_mediainfo');

require_once OUGC_MEDIAINFO_ROOT.'/core.php';

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Add our hooks
if(defined('IN_ADMINCP'))
{
	require_once OUGC_MEDIAINFO_ROOT.'/admin.php';

	require_once OUGC_MEDIAINFO_ROOT.'/admin_hooks.php';

	\OUGCMediaInfo\Core\addHooks('OUGCMediaInfo\AdminHooks');
}
else
{
	require_once OUGC_MEDIAINFO_ROOT.'/forum_hooks.php';

	\OUGCMediaInfo\Core\addHooks('OUGCMediaInfo\ForumHooks');
}

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

	public $tmdb_type = '';

	public $tmdbid = 0;

	public $imdbid = '';

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
	
			$plugins->add_hook('forumdisplay_before_thread', array($this, 'hook_forumdisplay_before_thread'));
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
					$templatelist .= ',ougcmediainfo_field, ougcmediainfo_description, ougcmediainfo_rating, ougcmediainfo, ougcmediainfo_postbit';
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
		return \OUGCMediaInfo\Admin\_info();
	}

	// Plugin API:_activate() routine
	function _activate()
	{
		\OUGCMediaInfo\Admin\_activate();
	}

	// Plugin API:_deactivate() routine
	function _deactivate()
	{
		\OUGCMediaInfo\Admin\_deactivate();
	}

	// Plugin API:_install() routine
	function _install()
	{
		\OUGCMediaInfo\Admin\_install();
	}

	// Plugin API:_is_installed() routine
	function _is_installed()
	{
		return \OUGCMediaInfo\Admin\_is_installed();
	}

	// Plugin API:_uninstall() routine
	function _uninstall()
	{
		\OUGCMediaInfo\Admin\_uninstall();
	}

	// Load language file
	function load_language()
	{
		\OUGCMediaInfo\Core\load_language();
	}

	// Build plugin info
	function load_plugin_info()
	{
		$this->plugin_info = ougc_mediainfo_info();
	}

	// PluginLibrary requirement check
	function load_pluginlibrary()
	{
		\OUGCMediaInfo\Core\load_pluginlibrary();
	}

	// List of tables
	function _db_tables()
	{
		return \OUGCMediaInfo\Admin\_db_tables();
	}

	// List of columns
	function _db_columns()
	{
		return \OUGCMediaInfo\Admin\_db_columns();
	}

	// Verify DB indexes
	function _db_verify_indexes()
	{
		return \OUGCMediaInfo\Admin\_db_verify_indexes();
	}

	// Verify DB tables
	function _db_verify_tables()
	{
		return \OUGCMediaInfo\Admin\_db_verify_tables();
	}

	// Verify DB columns
	function _db_verify_columns()
	{
		return \OUGCMediaInfo\Admin\_db_verify_columns();
	}

	// Hook: newthread_end
	function hook_newthread_end()
	{
		global $mybb, $fid, $templates, $ougc_mediainfo_input, $lang, $thread, $pid, $plugins;

		if(
			!\OUGCMediaInfo\Core\allowManualInput($fid) ||
			($plugins->current_hook == 'editpost_end' && $pid != $thread['firstpost']) ||
			$mybb->settings['ougc_mediainfo_fetchfrommessage']
		)
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

	function hook_datahandler_post_validate_post(&$dh)
	{
		global $mybb, $lang, $db, $plugins, $thread;

		if(!\OUGCMediaInfo\Core\allowManualInput($dh->data['fid']))
		{
			return;
		}

		$quickreply = in_array(THIS_SCRIPT, array('xmlhttp.php', 'newreply.php'));

		if(
			$plugins->current_hook == 'datahandler_post_validate_post' && !$dh->first_post ||
			!$mybb->settings['ougc_mediainfo_fetchfrommessage'] && $quickreply
		)
		{
			return;
		}

		$this->load_language();

		if($mybb->settings['ougc_mediainfo_fetchfrommessage'])
		{
			if($quickreply)
			{
				$input_key = 'value';
			}
			else
			{
				$input_key = 'message';
			}
		}
		else
		{
			$input_key = 'imdbid';
		}

		$this->imdbid = $this->get_imdbid($mybb->get_input($input_key, MyBB::INPUT_STRING));

		if(!$this->imdbid)
		{
			$this->tmdbid = $this->get_tmdbid($mybb->get_input($input_key, MyBB::INPUT_STRING));

			if(!$this->tmdbid)
			{
				if($mybb->settings['ougc_mediainfo_forceinput'])
				{
					$dh->set_error($lang->ougc_mediainfo_error_nomatch);
				}
		
				return;
			}
			else
			{
				$dh->ougc_mediainfo = $this->get_tmdbmedia($this->tmdbid);
			}
		}
		else
		{
			$dh->ougc_mediainfo = $this->get_imdbmedia($this->imdbid);
		}

		if(!$this->imdbid || empty($dh->ougc_mediainfo))
		{
			if($mybb->settings['ougc_mediainfo_forceinput'])
			{
				$dh->set_error($lang->ougc_mediainfo_error_apikey);
			}

			return;
		}

		$dh->data['imdbid'] = $this->imdbid;

		if($plugins->current_hook == 'datahandler_post_validate_post')
		{
			$dh->thread_update_data['imdbid'] = $this->imdbid;

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

		if(empty($thread['imdbid']) || !\OUGCMediaInfo\Core\allowManualInput($thread['fid']) || \OUGCMediaInfo\Core\allowMyCode())
		{
			return;
		}

		$imdbid = $db->escape_string($thread['imdbid']);

		$query = $db->simple_select('ougc_mediainfo', '*', "imdbid='{$imdbid}'");

		$media = $db->fetch_array($query);

		$ougc_mediainfo_display = $this->render($media);
	}

	function hook_forumdisplay_before_thread(&$args)
	{
		global $db, $cache;
		global $foruminfo;
		global $mediaInfoThreadsCache, $mediaInfoThreadsCacheCustom;

		isset($mediaInfoThreadsCache) || $mediaInfoThreadsCache = [];

		isset($mediaInfoThreadsCacheCustom) || $mediaInfoThreadsCacheCustom = [];

		if(\OUGCMediaInfo\Core\allowManualInput($foruminfo['fid']) || !\OUGCMediaInfo\Core\allowMyCode())
		{
			return;
		}

		$categories = $cache->read('ougc_mediainfo_categories');

		if(!empty($categories))
		{
			$categories = array_flip($categories);
		}
		else
		{
			$categories = [];
		}

		$tids = implode("','", array_map('intval', array_keys($args['tids'])));

		$query = $db->simple_select(
			"threads t LEFT JOIN {$db->table_prefix}posts p ON (p.pid=t.firstpost)",
			't.tid, p.pid, p.message',
			"t.tid IN ('{$tids}')"
		);

		while($thread = $db->fetch_array($query))
		{
			if(my_strpos($thread['message'], '['.\OUGCMediaInfo\Core\myCodeTag()) === false)
			{
				continue;
			}

			$imdbId = null;

			preg_match(
				'#\['.\OUGCMediaInfo\Core\myCodeTag().'(.*?)\](.+?)\[\/'.\OUGCMediaInfo\Core\myCodeTag().'\](\r\n?|\n?)#si',
				$thread['message'],
				$matches,
				PREG_OFFSET_CAPTURE
			);

			if(!empty($matches[1]) && !empty($matches[1][0]) && my_strpos($matches[1][0], '=') === 0)
			{
				$imdbId = ['imdbid' => $matches[2][0], 'mycode' => (int)$categories[ltrim($matches[1][0], '=')]];
			}
			elseif(!empty($matches[2][0]))
			{
				$imdbId = ['imdbid' => $matches[2][0], 'mycode' => null];
			}

			if(!empty($imdbId))
			{
				$mediaInfoThreadsCache[(int)$thread['tid']] = $imdbId;
			}
		}

		$cids = implode("','", array_column($mediaInfoThreadsCache, 'mycode'));

		$imdbIds = implode("','", array_map([$db, 'escape_string'], array_column($mediaInfoThreadsCache, 'imdbid')));

		$query = $db->simple_select(
			"ougc_mediainfo_categories_data d LEFT JOIN {$db->table_prefix}ougc_mediainfo m ON (m.mid=d.mid)",
			'd.*, m.imdbid',
			"d.cid IN ('{$cids}') AND m.imdbid IN ('{$imdbIds}')"
		);

		while($mediaData = $db->fetch_array($query))
		{
			if(!isset($mediaInfoThreadsCacheCustom[(int)$mediaData['cid']][$mediaData['imdbid']]))
			{
				$mediaInfoThreadsCacheCustom[(int)$mediaData['cid']][$mediaData['imdbid']] = $mediaData;
			}
		}
	}

	// Hook: 
	function hook_forumdisplay_thread_end()
	{
		global $mybb, $thread, $db, $ougc_mediainfo_id, $templates, $lang, $threadcache, $theme, $ougc_mediainfo_popup, $plugins;
		global $mediaInfoThreadsCache, $mediaInfoThreadsCacheCustom;

		$ougc_mediainfo_id = $ougc_mediainfo_popup = '';

		$myCode = 0;

		if(\OUGCMediaInfo\Core\allowMyCode())
		{
			if(!isset($mediaInfoThreadsCache[(int)$thread['tid']]))
			{
				return;
			}

			$thread['imdbid'] = $mediaInfoThreadsCache[(int)$thread['tid']]['imdbid'];
	
			$myCode = $mediaInfoThreadsCache[(int)$thread['tid']]['mycode'];
		}
		elseif(empty($thread['imdbid']) || !\OUGCMediaInfo\Core\allowManualInput($thread['fid']))
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

			$imdbids = implode("','", array_merge($imdbids, array_map([$db, 'escape_string'], array_column($mediaInfoThreadsCache, 'imdbid'))));

			$query = $db->simple_select('ougc_mediainfo', '*', "imdbid IN ('{$imdbids}')");

			while($media = $db->fetch_array($query))
			{
				$media_cache[$media['imdbid']] = $media;
			}
		}

		$media = $media_cache[$thread['imdbid']];

		if($myCode && $mediaInfoThreadsCacheCustom[$myCode][$thread['imdbid']])
		{
			$media = array_merge($media, $mediaInfoThreadsCacheCustom[$myCode][$thread['imdbid']]);
		}

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

		if(isset($mybb->input['forums']) && (string)$mybb->input['forums'][0] != 'all')
		{
			if(is_array($mybb->input['forums']))
			{
				$valid_forum = false;

				foreach($mybb->input['forums'] as $fid)
				{
					if(is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $fid, 'additionalgroups' => '')))
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
			elseif(!is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $mybb->get_input('forums', MyBB::INPUT_INT), 'additionalgroups' => '')))
			{
				return;
			}
		}

		$this->imdbid = $this->get_imdbid($mybb->get_input('keywords', MyBB::INPUT_STRING));

		if(empty($this->imdbid))
		{
			$tmdbid = $this->get_tmdbid($mybb->get_input('keywords', MyBB::INPUT_STRING));

			if(!empty($tmdbid))
			{
				$this->get_tmdbmedia($tmdbid);
			}
		}

		if(!$this->imdbid)
		{
			return;
		}

		$mybb->input['postthread'] = 1;

		$mybb->input['showresults'] = 'threads';

		$mybb->input['keywords'] = $this->imdbid;

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
		global $templates, $lang, $mybb, $theme;

		if(empty($media['mid']))
		{
			return;
		}
	
		$this->load_language();

		if(THIS_SCRIPT == 'forumdisplay.php')
		{
			$disettings = explode(',', $mybb->settings['ougc_mediainfo_fields_forumlist']);
		}
		else
		{
			$disettings = explode(',', $mybb->settings['ougc_mediainfo_fields_thread']);
		}

		$disettings = array_flip($disettings);

		foreach(array('mid', 'year', 'metascore', 'imdbvotes', 'released') as $field)
		{
			${$field} = '';

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
			${$field} = '';

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
			if(my_validate_url($media['image']))
			{
				$image_source = $media['image'];
			}
			elseif($mybb->settings['usecdn'] && !empty($mybb->settings['cdnurl']))
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

		$description = '';

		if(isset($media['description']))
		{
			$description = htmlspecialchars_uni($media['description']);

			$description = eval($templates->render('ougcmediainfo_description'));
		}

		return eval($templates->render('ougcmediainfo'));
	}

	function get_imdbmedia($imdbid)
	{
		global $mybb;

		if(!$this->imdbid)
		{
			$this->imdbid = $imdbid;
		}

		$json = file_get_contents("http://www.omdbapi.com/?i={$imdbid}&apikey={$mybb->settings['ougc_mediainfo_apikey']}");

		$omdb_data = json_decode($json, true);

		if(empty($omdb_data['Error']) && $omdb_data['Response'] != 'False')
		{
			foreach($omdb_data as $key => $value)
			{
				if(!is_array($value) && my_strtolower((string)$value) == 'n/a')
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

		if(!$this->tmdbid)
		{
			$this->get_tmdbid_by_imdbid($imdbid);
		}

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

	function get_tmdbmedia($id)
	{
		global $settings;

		if(!$this->tmdb_type)
		{
			$file = false;

			foreach(['movie', 'tv'] as $type)
			{

				$url = "https://api.themoviedb.org/3/{$type}/{$id}/external_ids?api_key={$settings['ougc_mediainfo_tmdbapikey']}";

				if($file = fetch_remote_file($url))
				{
					break;
				}
			}
		}
		else
		{
			$url = "https://api.themoviedb.org/3/{$this->tmdb_type}/{$id}/external_ids?api_key={$settings['ougc_mediainfo_tmdbapikey']}";

			$file = fetch_remote_file($url);
		}

		if(!$file)
		{
			return;
		}

		$data = (array)json_decode($file, true);

		if(!empty($data['imdb_id']))
		{
			$this->imdbid = (string)$data['imdb_id'];
		}

		return $this->get_imdbmedia($this->imdbid);
	}

	function insert_data($imdbid, $data)
	{
		global $mybb, $db;

		$imdbid = $db->escape_string($imdbid);

		$query = $db->simple_select('ougc_mediainfo', '*', "imdbid='{$imdbid}'");

		$update = (bool)$db->num_rows($query);

		$insert_data = array(
			'tmdbID'	=> $this->tmdbid
		);

		if(!empty($data['Released']))
		{
			$insert_data['released'] = (int)strtotime($data['Released']);
		}

		if(!empty($data['Ratings']))
		{
			$insert_data['ratings'] = $db->escape_string(my_serialize($data['Ratings']));
		}

		if(!empty($data['imdbVotes']))
		{
			$insert_data['imdbvotes'] = (int)str_replace(',', '', (string)$data['imdbVotes']);
		}

		foreach(['Title', 'Rated', 'Runtime', 'Genre', 'Director', 'Writer', 'Actors', 'Plot', 'Language', 'Country', 'Awards', 'Poster', 'Type', 'Production'] as $k)
		{
			if(isset($data[$k]))
			{
				$insert_data[my_strtolower($k)] = $db->escape_string($data[$k]);
			}
		}

		foreach(['Year', 'Metascore'] as $k)
		{
			if(isset($data[$k]))
			{
				$insert_data[my_strtolower($k)] = (int)$data[$k];
			}
		}

		foreach(['imdbRating'] as $k)
		{
			if(isset($data[$k]))
			{
				$insert_data[my_strtolower($k)] = (float)$data[$k];
			}
		}

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

		if(!$imdbid)
		{
			return false;
		}

		if($update)
		{
			$db->update_query('ougc_mediainfo', $insert_data, "imdbid='{$imdbid}'");
		}
		else
		{
			$insert_data['imdbid'] = $imdbid;

			$this->mid = $db->insert_query('ougc_mediainfo', $insert_data);
		}

		$images_path = MYBB_ROOT.'uploads/ougc_mediainfo';

		$ext = get_extension(my_strtolower((string)$data['Poster']));

		if(!is_writable($images_path) || !in_array($ext, array('gif', 'png', 'jpg', 'jpeg', 'jpe')) || !function_exists('curl_init'))
		{
			return;
		}

		if(!($image = @file_get_contents($data['Poster'])) || !($headers = get_headers($data['Poster'], 1)))
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
		$string = trim($string);

		preg_match('#^https://(?:www\.)?imdb\.com/title/(tt\\d{7,8})/$#', $string, $match);

		if(empty($match[1]))
		{
			preg_match('/tt\\d{7,8}/', $string, $match);

			if(isset($match[0]))
			{
				$imdbid = $match[0];
			}
		}
		else
		{
			$imdbid = $match[1];
		}

		if(empty($imdbid))
		{
			return false;
		}

		$this->imdbid = (string)$imdbid;

		return $this->imdbid;
	}

	function get_tmdbid($string)
	{
		$string = trim($string);

		preg_match('#themoviedb.org/(movie|tv)/(\\d+)($|-)#i', $string, $match);

		if($match)
		{
			$this->tmdb_type = (string)$match[1];

			return (int)$match[2];
		}

		return false;
	}

	function get_tmdbid_by_imdbid($imdbid)
	{
		global $settings;

		if(!$this->imdbid)
		{
			$this->imdbid = $imdbid;
		}

		$url = "http://api.themoviedb.org/3/find/{$imdbid}?api_key={$settings['ougc_mediainfo_tmdbapikey']}&external_source=imdb_id";

		if($file = fetch_remote_file($url))
		{
			$data = (array)json_decode($file, true);
		}

		$this->tmdbid = 0;

		if(!empty($data['movie_results']) && isset($data['movie_results'][0]))
		{
			$this->tmdbid = (int)$data['movie_results'][0]['id'];
		}

		if(!$this->tmdbid)
		{
			return false;
		}

		$this->get_tmdbmedia($this->tmdbid);
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