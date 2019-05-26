<?php

namespace App\ModelFunctions;

use App\Configs;
use App\Locale\Lang;

class ConfigFunctions
{
	/**
	 * return the basic information for a Page.
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
		$infos['additional_footer_text'] = Configs::get_value('additional_footer_text');
		if (Configs::get_value('site_copyright_begin') != Configs::get_value('site_copyright_end')) {
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
		return Configs::info()->pluck('value', 'key')->all();
	}

	/**
	 * Returns the public settings of Lychee.
	 *
	 * @return array
	 */
	public function public()
	{
		// Execute query
		return Configs::public()->pluck('value', 'key')->all();
	}

	/**
	 * Returns the admin settings of Lychee.
	 *
	 * @return array
	 */
	public function admin()
	{
		// Execute query
		$return = Configs::admin()->pluck('value', 'key')->all();
		$return['sortingPhotos'] = 'ORDER BY '.$return['sortingPhotos_col'].' '.$return['sortingPhotos_order'];
		$return['sortingAlbums'] = 'ORDER BY '.$return['sortingAlbums_col'].' '.$return['sortingAlbums_order'];

		$return['lang_available'] = Lang::get_lang_available();

		return $return;
	}
}
