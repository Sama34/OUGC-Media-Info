<?php

/***************************************************************************
 *
 *	OUGC Media Info plugin (/inc/plugins/ougc_mediainfo/forum_hooks.php)
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

namespace OUGCMediaInfo\ForumHooks;

function parse_message_end(&$message)
{
	global $post;

	if(!\OUGCMediaInfo\Core\allowMyCode() || my_strpos($message, '['.\OUGCMediaInfo\Core\myCodeTag()) === false || empty($post['pid']))
	{
		return;
	}

	static $mediaCache = [];

	preg_replace_callback(
		'#\['.\OUGCMediaInfo\Core\myCodeTag().'\](.+?)\[\/'.\OUGCMediaInfo\Core\myCodeTag().'\](\r\n?|\n?)#si',
		function ($matches) use (&$message) {
			if(empty($matches[0]))
			{
				return;
			}

			\OUGCMediaInfo\ForumHooks\_helper_parse($message, $matches[1]);
		},
		$message,
		-1,
		$count
	);

	preg_replace_callback(
		'#\['.\OUGCMediaInfo\Core\myCodeTag().'=(.+?)\](.+?)\[\/'.\OUGCMediaInfo\Core\myCodeTag().'\](\r\n?|\n?)#si',
		function ($matches) use (&$message) {
			if(empty($matches[0]))
			{
				return;
			}

			\OUGCMediaInfo\ForumHooks\_helper_parse($message, $matches[2], $matches[1]);
		},
		$message,
		-1,
		$countCustom
	);
}

function _helper_parse(&$message, $imdbId, $myCode=null)
{
	global $db, $templates, $theme, $cache, $lang;
	global $ougc_mediainfo;

	static $mediaCacheData = [];

	static $mediaCacheCategoriesData = [];

	static $mediaCache = [];

	if(!isset($mediaCacheData[$imdbId]))
	{
		$imdbIdEscaped = $db->escape_string(my_strtolower($imdbId));

		$query = $db->simple_select('ougc_mediainfo', '*', "LOWER(imdbid)='{$imdbIdEscaped}'");

		$mediaCacheData[$imdbId] = $db->fetch_array($query);
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

	if($myCode !== null && !isset($categories[$myCode]))
	{
		$myCode = 0;
	}

	if(!isset($mediaCache[$myCode][$imdbId]))
	{
		\OUGCMediaInfo\Core\load_language();

		$media = $mediaCacheData[$imdbId];

		if($myCode !== null && !isset($mediaCacheCategoriesData[$myCode][$imdbId]))
		{
			$cid = (int)$categories[$myCode];

			$mid = (int)$media['mid'];

			$query = $db->simple_select('ougc_mediainfo_categories_data', '*', "cid='{$cid}' AND mid='{$mid}'");
	
			$mediaCacheCategoriesData[$myCode][$imdbId] = $db->fetch_array($query);
		}

		if(isset($mediaCacheCategoriesData[$myCode][$imdbId]))
		{
			$media = array_merge($media, $mediaCacheCategoriesData[$myCode][$imdbId]);
		}

		$ougc_mediainfo_display = $ougc_mediainfo->render($media);

		$mediaCache[$myCode][$imdbId] = eval($templates->render('ougcmediainfo_postbit'));
	}

	if(!isset($mediaCache[$myCode][$imdbId]))
	{
		return;
	}

	if($myCode === null)
	{
		$message = str_replace(
			'['.\OUGCMediaInfo\Core\myCodeTag().']'.$imdbId.'[/'.\OUGCMediaInfo\Core\myCodeTag().']',
			$mediaCache[$myCode][$imdbId],
			$message
		);
	}
	else
	{
		$message = str_replace(
			'['.\OUGCMediaInfo\Core\myCodeTag().'='.$myCode.']'.$imdbId.'[/'.\OUGCMediaInfo\Core\myCodeTag().']',
			$mediaCache[$myCode][$imdbId],
			$message
		);
	}
}