<?php

namespace App\ModelFunctions;

use App\Configs;
use App\Locale\Lang;
use Illuminate\Database\QueryException;

class ConfigFunctions
{
	/**
	 * return the basic information for a Page.
	 *
	 * @return array
	 */
	public function get_pages_infos()
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
		$return = Configs::public()->pluck('value', 'key')->all();
		$return['sorting_Albums'] = 'ORDER BY ' . $return['sorting_Albums_col'] . ' ' . $return['sorting_Albums_order'];

		return $return;
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
		$return['sorting_Photos'] = 'ORDER BY ' . $return['sorting_Photos_col'] . ' ' . $return['sorting_Photos_order'];
		$return['sorting_Albums'] = 'ORDER BY ' . $return['sorting_Albums_col'] . ' ' . $return['sorting_Albums_order'];

		$return['lang_available'] = Lang::get_lang_available();

		return $return;
	}

	/**
	 * Sanity check of the config.
	 *
	 * @param array $return
	 */
	public function sanity(array &$return)
	{
		try {
			$configs = Configs::all(['key', 'value', 'type_range']);

			foreach ($configs as $config) {
				$message = $config->sanity($config->value);
				if ($message != '') {
					$return[] = $message;
				}
			}
		} catch (QueryException $e) {
			$return[] = 'Error: ' . $e->getMessage();
		}
	}

	public function get_config_device(string $device)
	{
		if ($device == 'television') {
			// @codeCoverageIgnoreStart
			return [
				'header_auto_hide' => false,
				'active_focus_on_page_load' => true,
				'enable_button_visibility' => false,
				'enable_button_share' => false,
				'enable_button_archive' => false,
				'enable_button_move' => false,
				'enable_button_trash' => false,
				'enable_button_fullscreen' => false,
				'enable_button_download' => false,
				'enable_button_add' => false,
				'enable_button_more' => false,
				'enable_button_rotate' => false,
				'enable_close_tab_on_esc' => true,
				'enable_contextmenu_header' => false,
				'hide_content_during_imgview' => true,
				'enable_tabindex' => true,
				'device_type' => $device,
			];
			// @codeCoverageIgnoreEnd
		}

		return [
			'header_auto_hide' => true,
			'active_focus_on_page_load' => false,
			'enable_button_visibility' => true,
			'enable_button_share' => true,
			'enable_button_archive' => true,
			'enable_button_move' => true,
			'enable_button_trash' => true,
			'enable_button_fullscreen' => true,
			'enable_button_download' => true,
			'enable_button_add' => true,
			'enable_button_more' => true,
			'enable_button_rotate' => true,
			'enable_close_tab_on_esc' => false,
			'enable_tabindex' => false,
			'enable_contextmenu_header' => true,
			'hide_content_during_imgview' => false,
			'device_type' => $device,
		];
	}
}
