<?php

namespace App\ModelFunctions;


use App\Configs;

class ConfigFunctions
{


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
		$sql = Configs::info();
		return Configs::arrayify($sql);

	}

	/**
	 * Returns the public settings of Lychee.
	 *
	 * @return array
	 */
	public function public()
	{

		// Execute query
		$sql = Configs::public();
		return Configs::arrayify($sql);

	}


	/**
	 * Returns the admin settings of Lychee.
	 *
	 * @return array
	 */
	public function admin()
	{

		// Execute query
		$sql = Configs::admin();
		return Configs::arrayify($sql);

	}
}