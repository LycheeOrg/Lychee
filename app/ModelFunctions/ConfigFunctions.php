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
		define('INT', 'int');
		define('STRING', 'string');
		define('STRING_REQ', 'string_required');
		define('BOOL', '0|1');
		define('TERNARY', '0|1|2');
		define('DISABLED', '');

		$val_range = [BOOL => explode('|', BOOL), TERNARY => explode('|', TERNARY)];

		$configs = Configs::all(['key', 'value', 'type_range']);
		foreach ($configs as $config) {
			switch ($config->type_range) {
				case DISABLED:
					break;
				case STRING:
					break;
				case STRING_REQ:
					if ($config->value == '') {
						$return[] = 'Error: ' . $config->key . ' empty or not set in database';
					}
					break;
				case INT:
					if (intval($config->value) < 0) {
						$return[] = 'Error: Wrong property for ' . $config->key . ' in database, expected positive integer.';
					}
					break;
				case BOOL:
				case TERNARY:
					if (!in_array($config->value, $val_range[$config->type_range])) { // BOOL or TERNARY
						$return[] = 'Error: Wrong property for ' . $config->key
							. ' in database, expected ' . implode(' or ',
								$val_range[$config->type_range]) . ', got ' . $config->value;
					}
					break;
				default:
					$values = explode('|', $config->type_range);
					if (!in_array($config->value, $values)) {
						$return[] = 'Error: Wrong property for ' . $config->key
							. ' in database, expected ' . implode(' or ', $values)
							. ', got ' . $config->value;
					}
					break;
			}
		}
	}
}
