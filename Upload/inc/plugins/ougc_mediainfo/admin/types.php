<?php

/***************************************************************************
 *
 *	OUGC Contest Logbook plugin (/inc/plugins/ougc_mediainfo/admin/logs.php)
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
defined('IN_MYBB') || die('This file cannot be accessed directly.');

$modules_dir = $modules_dir_backup;

$run_module = $run_module_backup;

$action_file = $action_file_backup;

\OUGCMediaInfo\Core\set_url('index.php?module=config-ougc_mediainfo');

\OUGCMediaInfo\Core\load_language();

global $lang;

$page->add_breadcrumb_item($lang->ougc_mediainfo_main_menu, \OUGCMediaInfo\Core\get_url());

$cid = $mybb->get_input('cid', \MyBB::INPUT_INT);

$mid = $mybb->get_input('mid', \MyBB::INPUT_INT);

$addLink = \OUGCMediaInfo\Core\build_url(['action' => 'add']);

if($mybb->get_input('action') == 'media')
{
	$addLink = \OUGCMediaInfo\Core\build_url(['action' => 'media', 'do' => 'add']);	
}

$sub_tabs = [
	'main' => [
		'title' => $lang->ougc_mediainfo_main_title,
		'link' => \OUGCMediaInfo\Core\get_url(),
		'description' => $lang->ougc_mediainfo_main_desc
	],
	'add' => [
		'title' => $lang->ougc_mediainfo_add_title,
		'link' => $addLink,
		'description' => $lang->ougc_mediainfo_add_desc
	],
	'media' => [
		'title' => $lang->ougc_mediainfo_media_title,
		'link' => \OUGCMediaInfo\Core\build_url(['action' => 'media']),
		'description' => $lang->ougc_mediainfo_media_desc
	],
];

$plugins->run_hooks("admin_config_ougc_mediainfo_begin");

if($mybb->get_input('action') == 'delete')
{
	if(!verify_post_check($mybb->get_input('my_post_key')))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');

		admin_redirect(\OUGCMediaInfo\Core\get_url());
	}

	$db->delete_query('ougc_mediainfo_categories_info', "cid='{$cid}'");

	$db->delete_query('ougc_mediainfo_categories', "cid='{$cid}'");

	flash_message($lang->ougc_mediainfo_delete_success, 'success');

	admin_redirect(\OUGCMediaInfo\Core\get_url());
}
elseif($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit')
{
	$addAction = ($action = $mybb->get_input('action')) == 'add';

	$editAction = !$addAction;

	$plugins->run_hooks("admin_ougc_mediainfo_{$action}_start");

	if($editAction)
	{
		if(!($logbook = \OUGCMediaInfo\Core\getCategory($cid)))
		{
			flash_message($lang->ougc_mediainfo_edit_error, 'error');
	
			admin_redirect(\OUGCMediaInfo\Core\get_url());
		}

		$page->add_breadcrumb_item($logbook['name']);

		$sub_tabs['edit'] = [
			'title' => $lang->ougc_mediainfo_edit_title,
			'link' => \OUGCMediaInfo\Core\build_url(['action' => 'edit', 'cid' => $cid]),
			'description' => $lang->ougc_mediainfo_edit_desc
		];
	}

	foreach(['name', 'mycodekey', 'enabled'] as $k)
	{
		if(!isset($logbook[$k]))
		{
			$logbook[$k] = '';
		}
	}
	$page->output_header($lang->{"ougc_mediainfo_{$action}_title"});

	$page->output_nav_tabs($sub_tabs, $action);

	if($mybb->request_method == 'post')
	{
		$errors = array();

		$logbook['name'] = $mybb->get_input('name');

		$logbook['mycodekey'] = $mybb->get_input('mycodekey');

		$logbook['enabled'] = $mybb->get_input('enabled', \MyBB::INPUT_INT);

		if(!$mybb->get_input('name') || isset($mybb->input['name']{150}))
		{
			$errors[] = $lang->ougc_mediainfo_edit_error_name;
		}

		if(ctype_alnum($logbook['mycodekey']))
		{
			$existing = \OUGCMediaInfo\Core\getCategoryByKey($logbook['mycodekey']);

			if($existing && $existing['cid'] != $cid)
			{
				$errors[] = $lang->ougc_mediainfo_edit_error_duplicated_mycodekey;
			}
		}
		else
		{
			$errors[] = $lang->ougc_mediainfo_edit_error_invalid_mycodekey;
		}

		if(empty($errors))
		{
			if($addAction)
			{
				\OUGCMediaInfo\Core\insertCategry(array(
					'name' => $logbook['name'],
					'mycodekey' => $logbook['mycodekey'],
					'enabled' => $logbook['enabled'],
				));
			}
			else
			{
				\OUGCMediaInfo\Core\updateCategory(array(
					'name' => $logbook['name'],
					'mycodekey' => $logbook['mycodekey'],
					'enabled' => $logbook['enabled'],
				), $cid);
			}
	
			\OUGCMediaInfo\Core\updateCache();

			flash_message($lang->{"ougc_mediainfo_{$action}_success"}, 'success');
		
			admin_redirect(\OUGCMediaInfo\Core\get_url());
		}
		else
		{
			$page->output_inline_error($errors);
		}
	}

	$form = new Form(\OUGCMediaInfo\Core\build_url([
		'action' => $action,
		'cid' => $cid
	]), 'post');

	$form_container = new FormContainer($lang->{"ougc_mediainfo_{$action}_title"});

	$form_container->output_row(
		$lang->ougc_mediainfo_form_name.' <em>*</em>',
		$lang->ougc_mediainfo_form_name_desc,
		$form->generate_text_box('name', $logbook['name'], ['class' => "\" maxlength=\"50"])
	);

	$form_container->output_row(
		$lang->ougc_mediainfo_form_mycodekey.' <em>*</em>',
		$lang->ougc_mediainfo_form_mycodekey_desc,
		$form->generate_text_box('mycodekey', $logbook['mycodekey'], ['class' => "\" maxlength=\"50"])
	);

	$form_container->output_row(
		$lang->ougc_mediainfo_form_enabled,
		$lang->ougc_mediainfo_form_enabled_desc,
		$form->generate_yes_no_radio('enabled', $logbook['enabled'])
	);

	$form_container->end();

	$form->output_submit_wrapper([
		$form->generate_submit_button($lang->ougc_mediainfo_form_button_submit),
		$form->generate_reset_button($lang->reset)
	]);

	$form->end();

	$page->output_footer();

	exit;
}
elseif($mybb->get_input('action') == 'media')
{
	if($mybb->get_input('do') == 'add' || $mybb->get_input('do') == 'edit')
	{
		$addAction = ($doAction = $mybb->get_input('do')) == 'add';
	
		$editAction = !$addAction;
	
		$plugins->run_hooks("admin_ougc_mediainfo_{$doAction}_start");
	
		if($editAction)
		{
			if(!($logbook = \OUGCMediaInfo\Core\getMedia($mid)))
			{
				flash_message($lang->ougc_mediainfo_edit_error, 'error');
		
				admin_redirect(\OUGCMediaInfo\Core\get_url());
			}
	
			$page->add_breadcrumb_item($logbook['title']);
	
			$sub_tabs['edit'] = [
				'title' => $lang->ougc_mediainfo_edit_title,
				'link' => \OUGCMediaInfo\Core\build_url(['action' => 'edit', 'mid' => $mid]),
				'description' => $lang->ougc_mediainfo_edit_desc
			];
		}

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

		foreach(array_merge(array_keys($dataKeys), $dataNumericKeys, $dataFloatKeys) as $k)
		{
			if(!isset($logbook[$k]))
			{
				$logbook[$k] = '';
			}
		}

		$page->output_header($lang->{"ougc_mediainfo_{$doAction}_title"});
	
		$page->output_nav_tabs($sub_tabs, $doAction);
	
		if($mybb->request_method == 'post')
		{
			$errors = array();

			foreach($dataKeys as $k => $limit)
			{
				if(isset($mybb->input[$k]))
				{
					$logbook[$k] = $mybb->get_input($k);
				}

				if($limit && isset($mybb->input[$k]{$limit}))
				{
					$errors[] = $lang->sprintf(
						$lang->ougc_mediainfo_add_error_invalid_size,
						$lang->{"ougc_mediainfo_form_media_{$k}"}
					);
				}
			}

			foreach($dataNumericKeys as $k)
			{
				if(isset($mybb->input[$k]))
				{
					$logbook[$k] = $mybb->get_input($k, \MyBB::INPUT_INT);
				}
			}

			foreach($dataFloatKeys as $k)
			{
				if(isset($mybb->input[$k]))
				{
					$logbook[$k] = $mybb->get_input($k, \MyBB::INPUT_FLOAT);
				}
			}

			if($addAction && $mybb->input['import'])
			{
				$ougc_mediainfo->imdbid = $ougc_mediainfo->get_imdbid($logbook['imdbid']);

				if(empty($ougc_mediainfo->imdbid))
				{
					$tmdbid = $ougc_mediainfo->get_tmdbid($logbook['tmdbid']);
		
					if(!empty($tmdbid))
					{
						$ougc_mediainfo->mediaInfo = $ougc_mediainfo->get_tmdbmedia($tmdbid);

						$logbook['tmdbid'] = $tmdbid;
					}
				}
				else
				{
					$ougc_mediainfo->mediaInfo = $ougc_mediainfo->get_imdbmedia($ougc_mediainfo->imdbid);
				}
		
				if(!empty($ougc_mediainfo->mediaInfo))
				{
					$logbook['imdbid'] = $ougc_mediainfo->imdbid;
				}
				else
				{
					$errors[] = $lang->ougc_mediainfo_add_error_invalid_media;
				}

				$query = $db->simple_select('ougc_mediainfo', '*', "imdbid='{$db->escape_string($ougc_mediainfo->imdbid)}'");

				if($db->num_rows($query))
				{
					$errors[] = $lang->ougc_mediainfo_add_error_duplicated_media;
				}
				else
				{
					$ougc_mediainfo->insert_data($ougc_mediainfo->imdbid, $ougc_mediainfo->mediaInfo);
	
					if(empty($ougc_mediainfo->mid))
					{
						$errors[] = $lang->ougc_mediainfo_add_error_invalid_media;
					}
					else
					{
						flash_message($lang->{"ougc_mediainfo_media_{$doAction}_success"}, 'success');
					
						admin_redirect(
							\OUGCMediaInfo\Core\build_url(['action' => 'media'])
						);
					}
				}
			}

			if(empty($errors))
			{
				$catsData = $mybb->get_input('categories', \MyBB::INPUT_ARRAY);

				if($addAction)
				{
					$mid = \OUGCMediaInfo\Core\insertMedia($logbook);
				}
				else
				{
					\OUGCMediaInfo\Core\updateMedia($logbook, $mid);
				}

				foreach($catsData as $cid => $data)
				{
					$cid = (int)$cid;

					if(!$data['image'] && !$data['description'])
					{
						\OUGCMediaInfo\Core\deleteMediaData($cid, $mid);
					}
					elseif(\OUGCMediaInfo\Core\getMediaData($cid, $mid))
					{
						\OUGCMediaInfo\Core\updateMediaData($data, $cid, $mid);
					}
					else
					{
						\OUGCMediaInfo\Core\insertMediaData($data, $cid, $mid);
					}
				}
	
				flash_message($lang->{"ougc_mediainfo_media_{$doAction}_success"}, 'success');
			
				admin_redirect(
					\OUGCMediaInfo\Core\build_url(['action' => 'media'])
				);
			}
			else
			{
				$page->output_inline_error($errors);
			}
		}
	
		$form = new Form(\OUGCMediaInfo\Core\build_url([
			'action' => 'media',
			'do' => $doAction,
			'mid' => $mid
		]), 'post');
	
		$form_container = new FormContainer($lang->{"ougc_mediainfo_{$doAction}_title"});

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_title.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_title_desc,
			$form->generate_text_area('title', $logbook['title'], ['maxlength' => 150])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_year.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_year_desc,
			$form->generate_numeric_field('year', $logbook['year'], ['min' => 0, 'max' => 2100])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_rated.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_rated_desc,
			$form->generate_text_box('rated', $logbook['rated'], ['class' => "\" maxlength=\"10"])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_released.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_released_desc,
			$form->generate_numeric_field('released', $logbook['released'], ['min' => 0, 'max' => 2100])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_runtime.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_runtime_desc,
			$form->generate_text_box('runtime', $logbook['runtime'], ['class' => "\" maxlength=\"10"])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_genre.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_genre_desc,
			$form->generate_text_area('genre', $logbook['genre'], ['maxlength' => 250])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_director.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_director_desc,
			$form->generate_text_area('director', $logbook['director'], ['maxlength' => 150])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_writer.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_writer_desc,
			$form->generate_text_area('writer', $logbook['writer'])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_actors.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_actors_desc,
			$form->generate_text_area('actors', $logbook['actors'])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_plot.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_plot_desc,
			$form->generate_text_area('plot', $logbook['plot'])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_language.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_language_desc,
			$form->generate_text_area('language', $logbook['language'], ['maxlength' => 250])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_country.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_country_desc,
			$form->generate_text_area('country', $logbook['country'], ['maxlength' => 150])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_awards.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_awards_desc,
			$form->generate_text_area('awards', $logbook['awards'], ['maxlength' => 150])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_poster.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_poster_desc,
			$form->generate_text_box('poster', $logbook['poster'], ['class' => "\" maxlength=\"200"])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_ratings.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_ratings_desc,
			$form->generate_text_area('ratings', $logbook['ratings'])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_metascore.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_metascore_desc,
			$form->generate_text_box('metascore', $logbook['metascore'], ['class' => "\" maxlength=\"5"])
		);//digit

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_imdbrating.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_imdbrating_desc,
			$form->generate_numeric_field('imdbrating', $logbook['imdbrating'], ['step' => '.01'])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_imdbvotes.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_imdbvotes_desc,
			$form->generate_numeric_field('imdbvotes', $logbook['imdbvotes'])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_imdbid.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_imdbid_desc,
			$form->generate_text_box('imdbid', $logbook['imdbid'], ['class' => "\" maxlength=\"15"])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_tmdbid.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_tmdbid_desc,
			$form->generate_text_box('tmdbid', $logbook['tmdbid'], ['min' => 0])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_type.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_type_desc,
			$form->generate_text_box('type', $logbook['type'], ['class' => "\" maxlength=\"15"])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_production.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_production_desc,
			$form->generate_text_box('production', $logbook['production'], ['class' => "\" maxlength=\"50"])
		);

		$form_container->output_row(
			$lang->ougc_mediainfo_form_media_image.' <em>*</em>',
			$lang->ougc_mediainfo_form_media_image_desc,
			$form->generate_text_box('image', $logbook['image'], ['class' => "\" maxlength=\"150"])
		);
	
		$form_container->end();

		$cids = $categories = $dataCache = [];

		$query = $db->simple_select('ougc_mediainfo_categories');

		while($cat = $db->fetch_array($query))
		{
			$categories[(int)$cat['cid']] = $cat;
		}

		$cids = array_keys($categories);

		$query = $db->simple_select('ougc_mediainfo_categories_data');

		while($row = $db->fetch_array($query))
		{
			$dataCache[(int)$row['mid']][(int)$row['cid']] = $row;
		}

		foreach($categories as $cid => $cat)
		{
			$cat['name'] = htmlspecialchars_uni($cat['name']);

			$cat['mycodekey'] = htmlspecialchars_uni($cat['mycodekey']);

			$form_container = new FormContainer("{$cat['name']} [{$cat['mycodekey']}]");

			$image = $description = '';

			if(isset($dataCache[$mid][$cid]))
			{
				$image = $dataCache[$mid][$cid]['image'];

				$description = $dataCache[$mid][$cid]['description'];
			}

			$form_container->output_row(
				$lang->ougc_mediainfo_form_media_image.' <em>*</em>',
				$lang->ougc_mediainfo_form_media_image_desc,
				$form->generate_text_box("categories[{$cid}][image]", $image, ['class' => "\" maxlength=\"150"])
			);

			$form_container->output_row(
				$lang->ougc_mediainfo_form_media_description.' <em>*</em>',
				$lang->ougc_mediainfo_form_media_description_desc,
				$form->generate_text_box("categories[{$cid}][description]", $description, ['class' => "\" maxlength=\"100"])
			);

			$form_container->end();
		}

		$buttons = [
			$form->generate_submit_button($lang->ougc_mediainfo_form_button_submit),
			$form->generate_reset_button($lang->reset)
		];

		if($addAction)
		{
			$buttons[] = $form->generate_submit_button($lang->ougc_mediainfo_form_button_submit_import, ['name' => 'import']);
		}

		$form->output_submit_wrapper($buttons);

		$form->end();
	
		$page->output_footer();
	
		exit;
	}
	else
	{

		$plugins->run_hooks('admin_ougc_mediainfo_media_start');
	
		$page->add_breadcrumb_item($lang->ougc_mediainfo_media_title, \OUGCMediaInfo\Core\get_url());
	
		$page->output_header($lang->ougc_mediainfo_media_title);
	
		$page->output_nav_tabs($sub_tabs, 'media');
	
		$query = $db->simple_select('ougc_mediainfo', '*');
	
		$table = new Table;
	
		$table->construct_header($lang->ougc_mediainfo_media_mediatitle, ['width' => '50%']);
	
		$table->construct_header($lang->ougc_mediainfo_media_imdbid, ['width' => '10%']);
	
		$table->construct_header($lang->options, ['class' => 'align_center', 'width' => '5%']);
	
		while($logbook = $db->fetch_array($query))
		{
			$mid = (int)$logbook['mid'];
	
			$table->construct_cell(htmlspecialchars_uni($logbook['title']));
	
			$table->construct_cell(htmlspecialchars_uni($logbook['imdbid']));
	
			$popup = new PopupMenu("log_{$mid}", $lang->options);
	
			$popup->add_item(
				$lang->edit,
				\OUGCMediaInfo\Core\build_url([
					'action' => 'media',
					'do' => 'edit',
					'mid' => $mid,
				])
			);
	
			$popup->add_item(
				$lang->delete,
				\OUGCMediaInfo\Core\build_url([
					'action' => 'delete',
					'mid' => $mid,
					'my_post_key' => $mybb->post_code
				]),
				"return AdminCP.deleteConfirmation(this, '{$lang->ougc_mediainfo_media_confirm_delete}')"
			);
	
			$table->construct_cell($popup->fetch(), ['class' => 'align_center']);
	
			$table->construct_row();
		}
	
		if($table->num_rows() == 0)
		{
			$table->construct_cell($lang->ougc_mediainfo_media_empty, ['class' => 'align_center', 'colspan' => 12]);
	
			$table->construct_row();
		}
	
		$table->output($lang->ougc_mediainfo_media_title);
	
		$page->output_footer();
	
		exit;
	}
}
else
{
	$plugins->run_hooks('admin_ougc_mediainfo_main_start');

	$page->add_breadcrumb_item($lang->ougc_mediainfo_main_title, \OUGCMediaInfo\Core\get_url());

	$page->output_header($lang->ougc_mediainfo_main_title);

	$page->output_nav_tabs($sub_tabs, 'main');

	$query = $db->simple_select('ougc_mediainfo_categories', '*');

	$table = new Table;

	$table->construct_header($lang->ougc_mediainfo_main_name, ['width' => '50%']);

	$table->construct_header($lang->ougc_mediainfo_main_enabled, ['class' => 'align_center']);

	$table->construct_header($lang->options, ['class' => 'align_center', 'width' => '5%']);

	while($logbook = $db->fetch_array($query))
	{
		$cid = (int)$logbook['cid'];

		$table->construct_cell(htmlspecialchars_uni($logbook['name']));

		$table->construct_cell($logbook['enabled'] ? $lang->yes : $lang->no);

		$popup = new PopupMenu("log_{$cid}", $lang->options);

		$popup->add_item(
			$lang->edit,
			\OUGCMediaInfo\Core\build_url([
				'action' => 'edit',
				'cid' => $cid,
			])
		);

		$popup->add_item(
			$lang->delete,
			\OUGCMediaInfo\Core\build_url([
				'action' => 'delete',
				'cid' => $cid,
				'my_post_key' => $mybb->post_code
			]),
			"return AdminCP.deleteConfirmation(this, '{$lang->ougc_mediainfo_main_confirm_delete}')"
		);

		$table->construct_cell($popup->fetch(), ['class' => 'align_center']);

		$table->construct_row();
	}

	if($table->num_rows() == 0)
	{
		$table->construct_cell($lang->ougc_mediainfo_main_empty, ['class' => 'align_center', 'colspan' => 12]);

		$table->construct_row();
	}

	$table->output($lang->ougc_mediainfo_main_title);

	$page->output_footer();

	exit;
}