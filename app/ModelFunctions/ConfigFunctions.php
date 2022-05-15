<?php

namespace App\ModelFunctions;

use App\Assets\Helpers;
use App\Exceptions\Internal\QueryBuilderException;
use App\Facades\Lang;
use App\Models\Configs;
use Illuminate\Support\Collection;

class ConfigFunctions
{
	/**
	 * return the basic information for a Page.
	 *
	 * @return array
	 */
	public function get_pages_infos(): array
	{
		$infos = [
			'owner' => Configs::get_value('landing_owner'),
			'title' => Configs::get_value('landing_title'),
			'subtitle' => Configs::get_value('landing_subtitle'),
			'facebook' => Configs::get_value('landing_facebook'),
			'flickr' => Configs::get_value('landing_flickr'),
			'twitter' => Configs::get_value('landing_twitter'),
			'instagram' => Configs::get_value('landing_instagram'),
			'youtube' => Configs::get_value('landing_youtube'),
			'background' => Configs::get_value('landing_background'),
			'login_background' => Configs::get_value('login_background'),
			'login_enable' => Configs::get_value('login_enable'),
			'copyright_enable' => Configs::get_value('site_copyright_enable'),
			'copyright_year' => Configs::get_value('site_copyright_begin'),
			'additional_footer_text' => Configs::get_value('additional_footer_text'),
		];
		if (Configs::get_value('site_copyright_begin') != Configs::get_value('site_copyright_end')) {
			$infos['copyright_year'] = Configs::get_value('site_copyright_begin') . '-' . Configs::get_value('site_copyright_end');
		}

		return $infos;
	}

	/**
	 * Returns the public settings of Lychee (served to diagnostics).
	 *
	 * @return Collection
	 *
	 * @throws QueryBuilderException
	 */
	public function min_info(): Collection
	{
		return Configs::info()
			->orderBy('id', 'ASC')
			->get()
			->pluck('value', 'key');
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
			if ($message != '') {
				$return[] = $message;
			}
		}
	}

	/**
	 * TODO: Get rid of this method.
	 *
	 * This method returns a hard-coded array of booleans which are flipped
	 * if the client is a television.
	 * However, the client knows by itself if it is a television or not.
	 * Hence, these values should be part of the front-end code.
	 *
	 * See also {@link Helpers::getDeviceType()}.
	 *
	 * @param string $device
	 *
	 * @return array
	 */
	public function get_config_device(string $device): array
	{
		$true = true;
		$false = false;

		// we just flip the values in the television case
		if ($device == 'television') {
			// @codeCoverageIgnoreStart
			$true = false;
			$false = true;
			// @codeCoverageIgnoreEnd
		}

		return [
			'header_auto_hide' => $true,
			'active_focus_on_page_load' => $false,
			'enable_button_visibility' => $true,
			'enable_button_share' => $true,
			'enable_button_archive' => $true,
			'enable_button_move' => $true,
			'enable_button_trash' => $true,
			'enable_button_fullscreen' => $true,
			'enable_button_download' => $true,
			'enable_button_add' => $true,
			'enable_button_more' => $true,
			'enable_button_rotate' => $true,
			'enable_close_tab_on_esc' => $false,
			'enable_contextmenu_header' => $true,
			'hide_content_during_imgview' => $false,
			'enable_tabindex' => $false,
			'device_type' => $device,
		];
	}
}
