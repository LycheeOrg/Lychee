<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;
use App\ModelFunctions\ConfigFunctions;
use App\Models\Configs;

class ConfigSanityCheck implements DiagnosticCheckInterface
{
	private ConfigFunctions $configFunctions;

	/**
	 * @param ConfigFunctions $configFunctions
	 */
	public function __construct(
		ConfigFunctions $configFunctions
	) {
		$this->configFunctions = $configFunctions;
	}

	public function check(array &$errors): void
	{
		// Load settings
		$settings = Configs::get();

		$keys_checked = [
			'sorting_photos_col', 'sorting_albums_col',
			'imagick', 'skip_duplicates', 'check_for_updates', 'version',
		];

		foreach ($keys_checked as $key) {
			if (!isset($settings[$key])) {
				$errors[] = 'Error: ' . $key . ' not set in database';
			}
		}

		/*
		 * Sanity check over all the variables
		 */
		$this->configFunctions->sanity($errors);

		// Check dropboxKey
		if (!isset($settings['dropbox_key'])) {
			$errors[]
				= 'Warning: Dropbox import not working. No property for dropbox_key.';
		} elseif ($settings['dropbox_key'] === '') {
			$errors[]
				= 'Warning: Dropbox import not working. dropbox_key is empty.';
		}
	}
}
