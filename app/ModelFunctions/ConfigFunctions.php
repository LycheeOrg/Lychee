<?php

namespace App\ModelFunctions;


use App\Configs;
use App\Logs;

class ConfigFunctions
{

	protected $clear_field = [
		'username',
		'password',
		'dropboxKey',

		'lang_available',
		'imagick',
		'skipDuplicates',
		'sortingAlbums',
		'sortingAlbums_col',
		'sortingAlbums_order',
		'sortingPhotos',
		'sortingPhotos_col',
		'sortingPhotos_order',
		'default_license',
		'thumb_2x',
		'small_max_width',
		'small_max_height',
		'small_2x',
		'medium_max_width',
		'medium_max_height',
		'medium_2x',
		'landing_title',
		'landing_background',
		'landing_facebook',
		'landing_flickr',
		'landing_twitter',
		'landing_youtube',
		'landing_instagram',
		'landing_owner',
		'landing_subtitle',
		'site_copyright_enable',
		'site_copyright_begin',
		'site_copyright_end'
	];


	/**
	 * return the basic informations for a Page.
	 *
	 * @return array
	 */
	public function get_pages_infos()
	{
		$infos = array();
		$infos['owner'] = Configs::get_value('landing_owner');
		$infos['title'] = Configs::get_value('landing_title');
		$infos['subtitle'] = Configs::get_value('landing_subtitle');
		$infos['facebook'] = Configs::get_value('landing_facebook');
		$infos['flickr'] = Configs::get_value('landing_flickr');
		$infos['twitter'] = Configs::get_value('landing_twitter');
		$infos['instagram'] = Configs::get_value('landing_instagram');
		$infos['youtube'] = Configs::get_value('landing_youtube');
		$infos['background'] = Configs::get_value('landing_background');
		$infos['copyright_enable'] = Configs::get_value('site_copyright_enable');
		$infos['copyright_year'] = Configs::get_value('site_copyright_begin');
		if (Configs::get_value('site_copyright_begin') != Configs::get_value('site_copyright_end'))
		{
			$infos['copyright_year'] = Configs::get_value('site_copyright_begin').'-'.Configs::get_value('site_copyright_end');
		}

		return $infos;
	}


	/**
	 * Returns the public settings of Lychee.
	 *
	 * @return array
	 */
	public function min_info()
	{

		// Execute query
		$configs = Configs::all();

		$return = array();

		// Add each to return
		foreach ($configs as $config) {
			$found = false;
			foreach ($this->clear_field as $exception) {
				if ($exception == $config->key) {
					$found = true;
				}
			}
			if (!$found) {
				$return[$config->key] = $config->value;
			}
		}
		return $return;
	}

}