<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Models\Configs;
use Illuminate\Support\Facades\Schema;

/**
 * Small checks on the content of the config database.
 * Mostly verifying that some keys exists.
 */
class ConfigSanityCheck implements DiagnosticPipe
{
	/** @var array<string,string> */
	private array $settings;

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('configs')) {
			return $next($data);
		}

		// Load settings
		$this->settings = Configs::get();

		$this->checkKeysExistsAndSet($data);

		$this->sanity($data);

		$this->checkDropBoxKeyWarning($data);

		return $next($data);
	}

	/**
	 * Check that a certain set of configuration exists in the database.
	 *
	 * @param array<int,string> $data
	 *
	 * @return void
	 */
	private function checkKeysExistsAndSet(array &$data): void
	{
		$keys_checked = [
			'sorting_photos_col', 'sorting_albums_col',
			'imagick', 'skip_duplicates', 'check_for_updates', 'version',
		];

		foreach ($keys_checked as $key) {
			if (!isset($this->settings[$key])) {
				$data[] = 'Error: ' . $key . ' not set in database';
			}
		}
	}

	/**
	 * Warning if the Dropbox key does not exists.
	 *
	 * @param array<int,string> $data
	 *
	 * @return void
	 */
	private function checkDropBoxKeyWarning(array &$data): void
	{
		$dropbox = Configs::getValueAsString('dropbox_key');
		if ($dropbox === '') {
			$data[] = 'Warning: Dropbox import not working. dropbox_key is empty.';
			$data[] = 'Info: To hide this Dropbox warning, set the dropbox_key to "disabled"';
		}
	}

	/**
	 * Sanity check of the config.
	 *
	 * @param array<int,string> $return
	 */
	private function sanity(array &$return): void
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
