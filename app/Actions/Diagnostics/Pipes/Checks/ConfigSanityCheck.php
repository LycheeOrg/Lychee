<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\ModelFunctions\ConfigFunctions;
use App\Models\Configs;
use Closure;

class ConfigSanityCheck implements DiagnosticPipe
{
	private ConfigFunctions $configFunctions;

	private array $settings;

	/**
	 * @param ConfigFunctions $configFunctions
	 */
	public function __construct(
		ConfigFunctions $configFunctions
	) {
		$this->configFunctions = $configFunctions;
	}

	public function handle(array &$data, Closure $next): array
	{
		// Load settings
		$this->settings = Configs::get();

		$this->checkKeysExistsAndSet($data);

		$this->configFunctions->sanity($data);

		$this->checkDropBoxKeyWarning($data);

		return $next($data);
	}

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

	private function checkDropBoxKeyWarning(array &$data): void
	{
		if (!isset($this->settings['dropbox_key'])) {
			$data[]
				= 'Warning: Dropbox import not working. No property for dropbox_key.';
		} elseif ($this->settings['dropbox_key'] === '') {
			$data[]
				= 'Warning: Dropbox import not working. dropbox_key is empty.';
		}
	}
}
