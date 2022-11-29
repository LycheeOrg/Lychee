<?php

namespace App\ModelFunctions;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Facades\Lang;
use App\Models\Configs;

class ConfigFunctions
{
	/**
	 * return the basic information for a Page.
	 *
	 * @return array
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function get_pages_infos(): array
	{
		$infos = [
			'owner' => Configs::getValueAsString('site_owner'),
			'title' => Configs::getValueAsString('landing_title'),
			'subtitle' => Configs::getValueAsString('landing_subtitle'),
			'facebook' => Configs::getValueAsString('sm_facebook_url'),
			'flickr' => Configs::getValueAsString('sm_flickr_url'),
			'twitter' => Configs::getValueAsString('sm_twitter_url'),
			'instagram' => Configs::getValueAsString('sm_instagram_url'),
			'youtube' => Configs::getValueAsString('sm_youtube_url'),
			'background' => Configs::getValueAsString('landing_background'),
			'copyright_enable' => Configs::getValueAsString('footer_show_copyright'),
			'copyright_year' => Configs::getValueAsString('site_copyright_begin'),
			'additional_footer_text' => Configs::getValueAsString('footer_additional_text'),
		];
		if (Configs::getValueAsString('site_copyright_begin') !== Configs::getValueAsString('site_copyright_end')) {
			$infos['copyright_year'] = Configs::getValueAsString('site_copyright_begin') . '-' . Configs::getValueAsString('site_copyright_end');
		}

		return $infos;
	}

	/**
	 * Returns the public settings of Lychee (served to the user).
	 *
	 * @return array
	 */
	public function public(): array
	{
		return Configs::public()->pluck('value', 'key')->all();
	}

	/**
	 * Returns the admin settings of Lychee.
	 *
	 * @return array
	 */
	public function admin(): array
	{
		$return = Configs::admin()->pluck('value', 'key')->all();
		$return['lang_available'] = Lang::get_lang_available();

		return $return;
	}

	/**
	 * Sanity check of the config.
	 *
	 * @param array $return
	 */
	public function sanity(array &$return): void
	{
		$configs = Configs::all(['key', 'value', 'type_range']);

		foreach ($configs as $config) {
			$message = $config->sanity($config->value);
			if ($message !== '') {
				$return[] = $message;
			}
		}
	}
}
