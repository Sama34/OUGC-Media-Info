<?php

/***************************************************************************
 *
 *	OUGC Media Info plugin (/inc/plugins/ougc_mediainfo/core.php)
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

namespace OUGCMediaInfo\Core;

function load_language($force=false)
{
	global $lang;

	isset($lang->setting_group_ougc_mediainfo) or $lang->load('ougc_mediainfo');
}

function load_pluginlibrary($check=true)
{
	global $lang;

	$pluginInfo = ougc_mediainfo_info();

	\OUGCMediaInfo\Core\load_language();

	if($file_exists = file_exists(PLUGINLIBRARY))
	{
		global $PL;

		$PL or require_once PLUGINLIBRARY;
	}

	if(!$file_exists || $PL->version < $pluginInfo['pl']['version'])
	{
		flash_message($lang->sprintf($lang->ougc_mediainfo_pluginlibrary, $pluginInfo['pl']['ulr'], $pluginInfo['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
	}
}

function addHooks(string $namespace)
{
    global $plugins;

    $namespaceLowercase = strtolower($namespace);
    $definedUserFunctions = get_defined_functions()['user'];

	foreach($definedUserFunctions as $callable)
	{
        $namespaceWithPrefixLength = strlen($namespaceLowercase) + 1;

		if(substr($callable, 0, $namespaceWithPrefixLength) == $namespaceLowercase.'\\')
		{
            $hookName = substr_replace($callable, null, 0, $namespaceWithPrefixLength);

            $priority = substr($callable, -2);

			if(is_numeric(substr($hookName, -2)))
			{
                $hookName = substr($hookName, 0, -2);
			}
			else
			{
                $priority = 10;
            }

            $plugins->add_hook($hookName, $callable, $priority);
        }
    }
}

// Set url
function set_url($url=null)
{
	static $current_url = '';

	if(($url = trim($url)))
	{
		$current_url = $url;
	}

	return $current_url;
}

// Set url
function get_url()
{
	return set_url();
}

// Build an url parameter
function build_url($urlappend=[])
{
	global $PL;

	\OUGCMediaInfo\Core\load_pluginlibrary(false);

	if(!is_object($PL))
	{
		return get_url();
	}

	if($urlappend && !is_array($urlappend))
	{
		$urlappend = explode('=', $urlappend);

		$urlappend = [$urlappend[0] => $urlappend[1]];
	}

	return $PL->url_append(get_url(), $urlappend, '&amp;', true);
}

function insertCategry($data, $cid=null)
{
	global $db;

	$insertData = [];

	if(isset($data['name']))
	{
		$insertData['name'] = $db->escape_string($data['name']);
	}

	if(isset($data['mycodekey']))
	{
		$insertData['mycodekey'] = $db->escape_string($data['mycodekey']);
	}

	if(isset($data['enabled']))
	{
		$insertData['enabled'] = $db->escape_string($data['enabled']);
	}

	if(!$insertData)
	{
		return false;
	}

	if($cid !== null)
	{
		$cid = (int)$cid;

		return $db->update_query('ougc_mediainfo_categories', $insertData, "cid='{$cid}'");
	}

	return $db->insert_query('ougc_mediainfo_categories', $insertData);
}

function updateCategory($data, $cid)
{
	return insertCategry($data, $cid);
}

function updateCache()
{
	global $cache, $db;

	$updateData = [];

	$query = $db->simple_select('ougc_mediainfo_categories', '*', 'enabled=1');

	while($cat = $db->fetch_array($query))
	{
		$updateData[(int)$cat['cid']] = $cat['mycodekey'];
	}

	$cache->update('ougc_mediainfo_categories', $updateData);
}

function getCategory($cid)
{
	global $db;

	$cid = (int)$cid;

	$query = $db->simple_select('ougc_mediainfo_categories', '*', "cid='{$cid}'");

	if($db->num_rows($query))
	{
		return $db->fetch_array($query);
	}

	return false;
}

function getCategoryByKey($mycodekey)
{
	global $db;

	$mycodekey = $db->escape_string($mycodekey);

	$query = $db->simple_select('ougc_mediainfo_categories', '*', "mycodekey='{$mycodekey}'");

	if($db->num_rows($query))
	{
		return $db->fetch_array($query);
	}

	return false;
}

function getMedia($mid)
{
	global $db;

	$mid = (int)$mid;

	$query = $db->simple_select('ougc_mediainfo', '*', "mid='{$mid}'");

	if($db->num_rows($query))
	{
		return $db->fetch_array($query);
	}

	return false;
}

function insertMedia($data, $mid=null)
{
	global $db;

	$insertData = [];

	$dataKeys = [
		'title' => 150,
		'rated' => 10,
		'runtime' => 10,
		'genre' => 250,
		'director' => 150,
		'writer' => 0,
		'actors' => 0,
		'plot' => 0,
		'language' => 250,
		'country' => 150,
		'awards' => 150,
		'poster' => 200,
		'ratings' => 0,
		'imdbid' => 15,
		'type' => 15,
		'production' => 50,
		'image' => 150,
		'tmdbid' => 0,
	];

	$dataNumericKeys = [
		'year',
		'released',
		'metascore',
		'imdbvotes',
	];

	$dataFloatKeys = [
		'imdbrating',
	];

	foreach($dataKeys as $k => $limit)
	{
		if(isset($data[$k]))
		{
			$insertData[$k] = $db->escape_string($data[$k]);
		}
	}

	foreach($dataNumericKeys as $k)
	{
		if(isset($data[$k]))
		{
			$insertData[$k] = (int)$data[$k];
		}
	}

	foreach($dataFloatKeys as $k)
	{
		if(isset($data[$k]))
		{
			$insertData[$k] = (float)$data[$k];
		}
	}

	if(!$insertData)
	{
		return false;
	}

	if($mid !== null)
	{
		$mid = (int)$mid;

		return $db->update_query('ougc_mediainfo', $insertData, "mid='{$mid}'");
	}

	global $ougc_mediainfo;

	$ougc_mediainfo->insert_data($data['imdbid'], $ougc_mediainfo->mediaInfo);

	return $ougc_mediainfo->mid;
}

function updateMedia($data, $mid)
{
	return insertMedia($data, $mid);
}

function deleteMediaData($cid, $mid)
{
	global $db;

	$cid = (int)$cid;

	$mid = (int)$mid;

	return $db->delete_query('ougc_mediainfo_categories_data', "cid='{$cid}' AND mid='{$mid}'");
}

function getMediaData($cid, $mid)
{
	global $db;

	$cid = (int)$cid;

	$mid = (int)$mid;

	$query = $db->simple_select('ougc_mediainfo_categories_data', '*', "cid='{$cid}' AND mid='{$mid}'");

	if($db->num_rows($query))
	{
		return $db->fetch_array($query);
	}

	return false;
}

function insertMediaData($data, $cid, $mid)
{
	global $db;

	$cid = (int)$cid;

	$mid = (int)$mid;

	$insertData = [
		'cid' => $cid,
		'mid' => $mid,
	];

	if(isset($data['image']))
	{
		$insertData['image'] = $db->escape_string($data['image']);
	}

	if(isset($data['description']))
	{
		$insertData['description'] = $db->escape_string($data['description']);
	}

	if(!$insertData)
	{
		return false;
	}

	if(getMediaData($cid, $mid))
	{
		return $db->update_query('ougc_mediainfo_categories_data', $insertData, "cid='{$cid}' AND mid='{$mid}'");
	}

	return $db->insert_query('ougc_mediainfo_categories_data', $insertData);
}

function updateMediaData($data, $cid, $mid)
{
	return insertMediaData($data, $cid, $mid);
}

function allowManualInput($fid=null)
{
	global $mybb;

	if(empty($mybb->settings['ougc_mediainfo_enablemanual']))
	{
		return false;
	}

	if(!empty($fid) && !is_member($mybb->settings['ougc_mediainfo_forums'], array('usergroup' => $fid, 'additionalgroups' => '')))
	{
		return false;
	}

	return true;
}

function allowMyCode()
{
	global $mybb;

	return !empty($mybb->settings['ougc_mediainfo_allowmycode']) && !empty($mybb->settings['ougc_mediainfo_mycodetag']);
}

function myCodeTag()
{
	global $mybb;

	return !empty($mybb->settings['ougc_mediainfo_mycodetag']) ? (string)$mybb->settings['ougc_mediainfo_mycodetag'] : 'mediainfo';
}