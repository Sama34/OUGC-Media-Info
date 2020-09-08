<?php

/***************************************************************************
 *
 *	OUGC Media Info plugin (/inc/languages/english/ougc_mediainfo.php)
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

// Front-end
$l['ougc_mediainfo_input'] = 'IMDB Link';
$l['ougc_mediainfo_input_desc'] = 'Insert the IMDB or TMDB link to the media you want to embed. Example: <code>https://www.imdb.com/title/tt4154796/</code> or <code>https://www.themoviedb.org/movie/862-toy-story</code>';
$l['ougc_mediainfo_input_placeholder'] = 'Insert IMDB or TMDB link...';

$l['ougc_mediainfo_error_nomatch'] = 'The IMDB link doesn\'s match the pattern. Please verify the provided IMDB link.';
$l['ougc_mediainfo_error_apikey'] = 'There was an error fetching the necessary data from the OMDb database. Please contact a moderator or administrator.';

$l['ougc_mediainfo_field_year'] = 'Year:';
$l['ougc_mediainfo_field_metascore'] = 'Meta:';
$l['ougc_mediainfo_field_imdbvotes'] = 'Votes:';
$l['ougc_mediainfo_field_released'] = 'Release date:';
$l['ougc_mediainfo_field_title'] = 'Title:';
$l['ougc_mediainfo_field_rated'] = 'Rate:';
$l['ougc_mediainfo_field_runtime'] = 'Run time:';
$l['ougc_mediainfo_field_genre'] = 'Genre:';
$l['ougc_mediainfo_field_director'] = 'Director:';
$l['ougc_mediainfo_field_writer'] = 'Writer:';
$l['ougc_mediainfo_field_actors'] = 'Actors:';
$l['ougc_mediainfo_field_plot'] = 'Plot:';
$l['ougc_mediainfo_field_language'] = 'Language:';
$l['ougc_mediainfo_field_country'] = 'County:';
$l['ougc_mediainfo_field_awards'] = 'Awards:';
$l['ougc_mediainfo_field_type'] = 'Type:';
$l['ougc_mediainfo_field_production'] = 'Production:';
$l['ougc_mediainfo_field_ratings'] = 'Ratings:';

$l['ougc_mediainfo_search'] = 'search IMDB or TMDB threads only';