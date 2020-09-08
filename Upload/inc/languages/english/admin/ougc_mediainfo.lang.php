<?php

/***************************************************************************
 *
 *	OUGC Media Info plugin (/inc/languages/english/admin/ougc_mediainfo.php)
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
 
// Plugin APIC
$l['setting_group_ougc_mediainfo'] = 'OUGC Media Info';
$l['setting_group_ougc_mediainfo_desc'] = 'Fetches films, television programs, home videos, video games, and streaming content online information to display in threads.';

// PluginLibrary
$l['ougc_mediainfo_pluginlibrary'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or newer. Please upload the required files.';

// Settings
$l['setting_ougc_mediainfo_forums'] = 'Allowed Forums';
$l['setting_ougc_mediainfo_forums_desc'] = 'Select the forums where this this feature is enabled.';
$l['setting_ougc_mediainfo_apikey'] = 'OMDb API';
$l['setting_ougc_mediainfo_apikey_desc'] = 'An OMDb API is required for this plugin to work. Please type your OMDb API key below.';
$l['setting_ougc_mediainfo_tmdbapikey'] = 'TMDB API';
$l['setting_ougc_mediainfo_tmdbapikey_desc'] = 'A TMDB API is required for users to be able to post TMDB links. Please type your OMDb API key below.';
$l['setting_ougc_mediainfo_fields_thread'] = 'Fields to Display in Thread';
$l['setting_ougc_mediainfo_fields_thread_desc'] = 'Select the fields to display in the show thread page.';
$l['setting_ougc_mediainfo_fields_forumlist'] = 'Fields to Display in Forum List';
$l['setting_ougc_mediainfo_fields_forumlist_desc'] = 'Select the fields to display in the thread list popup.';
$l['setting_ougc_mediainfo_fields_title'] = 'Title';
$l['setting_ougc_mediainfo_fields_year'] = 'Year';
$l['setting_ougc_mediainfo_fields_released'] = 'Released date';
$l['setting_ougc_mediainfo_fields_runtime'] = 'Run time';
$l['setting_ougc_mediainfo_fields_genre'] = 'Genre';
$l['setting_ougc_mediainfo_fields_director'] = 'Director';
$l['setting_ougc_mediainfo_fields_writer'] = 'Writer';
$l['setting_ougc_mediainfo_fields_actors'] = 'Actors';
$l['setting_ougc_mediainfo_fields_plot'] = 'Plot';
$l['setting_ougc_mediainfo_fields_language'] = 'Language';
$l['setting_ougc_mediainfo_fields_country'] = 'Country';
$l['setting_ougc_mediainfo_fields_awards'] = 'Awards';
$l['setting_ougc_mediainfo_fields_type'] = 'Type';
$l['setting_ougc_mediainfo_fields_production'] = 'Production';
$l['setting_ougc_mediainfo_fields_metascore'] = 'Meta score';
$l['setting_ougc_mediainfo_fields_imdbvotes'] = 'IMDB votes';
$l['setting_ougc_mediainfo_fields_rated'] = 'Rate';
$l['setting_ougc_mediainfo_fields_rating_list'] = 'Rating list';
$l['setting_ougc_mediainfo_enablesearch'] = 'Enable Search';
$l['setting_ougc_mediainfo_enablesearch_desc'] = 'Enable users to search by using the IMDB full URL or ID.';