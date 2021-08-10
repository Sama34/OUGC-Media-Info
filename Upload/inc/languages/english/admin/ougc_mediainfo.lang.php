<?php

/***************************************************************************
 *
 *	OUGC Media Info plugin (/inc/languages/english/admin/ougc_mediainfo.lang.php)
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
$l['setting_ougc_mediainfo_fetchfrommessage'] = 'Fetch From Message';
$l['setting_ougc_mediainfo_fetchfrommessage_desc'] = 'Enable this to fetch the media link from the message field isntead of the custom thread field.';
$l['setting_ougc_mediainfo_forceinput'] = 'Force Thread Media';
$l['setting_ougc_mediainfo_forceinput_desc'] = 'Turn this on to force users to provide a media URL for threads. If disabled, media URLs will be optional.';
$l['setting_ougc_mediainfo_allowmycode'] = 'Allow MyCode Style';
$l['setting_ougc_mediainfo_allowmycode_desc'] = 'Allow users to embed media information in posts using a MyCode tag.';
$l['setting_ougc_mediainfo_mycodetag'] = 'MyCode Tag';
$l['setting_ougc_mediainfo_mycodetag_desc'] = 'Select a custom MyCode tag to allow embed of media information in posts. Default is <code>mediainfo</code>';
$l['setting_ougc_mediainfo_enablemanual'] = 'Enable Manual Input';
$l['setting_ougc_mediainfo_enablemanual_desc'] = 'If disabled, only MyCode use will be allowed. This will require administrators to manually add media information into the DB.';

// Forms
$l['ougc_mediainfo_main_menu'] = 'Media Info';
$l['ougc_mediainfo_permissions'] = 'Can manage media info?';

$l['ougc_mediainfo_main_title'] = 'Categories';
$l['ougc_mediainfo_main_desc'] = 'Manage your media categories below.';
$l['ougc_mediainfo_main_name'] = 'Name';
$l['ougc_mediainfo_main_enabled'] = 'Enabled';
$l['ougc_mediainfo_main_empty'] = 'There are currently no categories to display.';
$l['ougc_mediainfo_main_confirm_delete'] = 'Are you sure you want to permanently delete this category?';

$l['ougc_mediainfo_delete_success'] = 'The selected category was successfully deleted.';

$l['ougc_mediainfo_media_title'] = 'Media';
$l['ougc_mediainfo_media_desc'] = 'Below is a list of media info stored in your database.';
$l['ougc_mediainfo_media_mediatitle'] = 'Title';
$l['ougc_mediainfo_media_imdbid'] = 'IMDB ID';
$l['ougc_mediainfo_media_enabled'] = 'Enabled';
$l['ougc_mediainfo_media_empty'] = 'There is currently no media to display.';
$l['ougc_mediainfo_media_confirm_delete'] = 'Are you sure you want to permanently delete this media?';

$l['ougc_mediainfo_form_media_title'] = 'Title';
$l['ougc_mediainfo_form_media_title_desc'] = '';
$l['ougc_mediainfo_form_media_year'] = 'Year';
$l['ougc_mediainfo_form_media_year_desc'] = '';
$l['ougc_mediainfo_form_media_rated'] = 'Rate';
$l['ougc_mediainfo_form_media_rated_desc'] = '';
$l['ougc_mediainfo_form_media_released'] = 'Release Date';
$l['ougc_mediainfo_form_media_released_desc'] = '';
$l['ougc_mediainfo_form_media_runtime'] = 'Run Time';
$l['ougc_mediainfo_form_media_runtime_desc'] = '';
$l['ougc_mediainfo_form_media_genre'] = 'Genre';
$l['ougc_mediainfo_form_media_genre_desc'] = '';
$l['ougc_mediainfo_form_media_director'] = 'Director';
$l['ougc_mediainfo_form_media_director_desc'] = '';
$l['ougc_mediainfo_form_media_writer'] = 'Writer';
$l['ougc_mediainfo_form_media_writer_desc'] = '';
$l['ougc_mediainfo_form_media_actors'] = 'Actors';
$l['ougc_mediainfo_form_media_actors_desc'] = '';
$l['ougc_mediainfo_form_media_plot'] = 'Plot';
$l['ougc_mediainfo_form_media_plot_desc'] = '';
$l['ougc_mediainfo_form_media_language'] = 'Language';
$l['ougc_mediainfo_form_media_language_desc'] = '';
$l['ougc_mediainfo_form_media_country'] = 'Country';
$l['ougc_mediainfo_form_media_country_desc'] = '';
$l['ougc_mediainfo_form_media_awards'] = 'Awards';
$l['ougc_mediainfo_form_media_awards_desc'] = '';
$l['ougc_mediainfo_form_media_poster'] = 'Poster';
$l['ougc_mediainfo_form_media_poster_desc'] = '';
$l['ougc_mediainfo_form_media_ratings'] = 'Ratings';
$l['ougc_mediainfo_form_media_ratings_desc'] = '';
$l['ougc_mediainfo_form_media_metascore'] = 'Meta Score';
$l['ougc_mediainfo_form_media_metascore_desc'] = '';
$l['ougc_mediainfo_form_media_imdbrating'] = 'IMDB Rating';
$l['ougc_mediainfo_form_media_imdbrating_desc'] = '';
$l['ougc_mediainfo_form_media_imdbvotes'] = 'Votes';
$l['ougc_mediainfo_form_media_imdbvotes_desc'] = '';
$l['ougc_mediainfo_form_media_imdbid'] = 'IMDB ID';
$l['ougc_mediainfo_form_media_imdbid_desc'] = '';
$l['ougc_mediainfo_form_media_tmdbid'] = 'TMDB ID';
$l['ougc_mediainfo_form_media_tmdbid_desc'] = '';
$l['ougc_mediainfo_form_media_type'] = 'Type';
$l['ougc_mediainfo_form_media_type_desc'] = '';
$l['ougc_mediainfo_form_media_production'] = 'Production';
$l['ougc_mediainfo_form_media_production_desc'] = '';
$l['ougc_mediainfo_form_media_image'] = 'Image URL';
$l['ougc_mediainfo_form_media_image_desc'] = '';

$l['ougc_mediainfo_form_media_description'] = 'Description';
$l['ougc_mediainfo_form_media_description_desc'] = '';
$l['ougc_mediainfo_form_media_description_desc'] = '';
$l['ougc_mediainfo_add_error_invalid_size'] = 'The {1} field is invalid.';
$l['ougc_mediainfo_add_error_invalid_media'] = 'The selected media could not be found.';
$l['ougc_mediainfo_add_error_duplicated_media'] = 'The selected media is duplicated.';
$l['ougc_mediainfo_media_add_success'] = 'The selected media was successfully added.';
$l['ougc_mediainfo_media_edit_success'] = 'The selected media was successfully updated.';






$l['ougc_mediainfo_add_title'] = 'Add';
$l['ougc_mediainfo_add_desc'] = 'Add a new category below.';
$l['ougc_mediainfo_add_success'] = 'The selected category was successfully added.';

$l['ougc_mediainfo_edit_title'] = 'Edit';
$l['ougc_mediainfo_edit_desc'] = 'Edit your category below.';
$l['ougc_mediainfo_edit_error'] = 'The selected category is invalid.';
$l['ougc_mediainfo_edit_error_name'] = 'The selected name is invalid. Maximum characters is 150.';
$l['ougc_mediainfo_edit_error_duplicated_mycodekey'] = 'The selected MyCode Key is duplicated.';
$l['ougc_mediainfo_edit_error_invalid_mycodekey'] = 'The selected MyCode Key is invalid.';
$l['ougc_mediainfo_edit_success'] = 'The selected category was successfully updated.';

$l['ougc_mediainfo_form_name'] = 'Name';
$l['ougc_mediainfo_form_name_desc'] = 'Select a name for this category.';
$l['ougc_mediainfo_form_mycodekey'] = 'MyCode Key';
$l['ougc_mediainfo_form_mycodekey_desc'] = 'Input a unique MyCode variant for this category.';
$l['ougc_mediainfo_form_enabled'] = 'Enabled';
$l['ougc_mediainfo_form_enabled_desc'] = 'If set to <code>No</code> this category will appear as <i>nonexistent</i>. Only alphanumerical characters allowed (0-9/A-Z).';
$l['ougc_mediainfo_form_button_submit'] = 'Submit';
$l['ougc_mediainfo_form_button_submit_import'] = 'Import from IMDB or TMDB';
