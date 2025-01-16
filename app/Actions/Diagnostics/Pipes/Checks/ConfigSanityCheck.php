<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Models\Configs;
use Illuminate\Support\Facades\Schema;

/**
 * Small checks on the content of the config database.
 * Mostly verifying that some keys exists.
 */
class ConfigSanityCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('configs')) {
			return $next($data);
		}

		$this->sanity($data);
		$this->checkDropBoxKeyWarning($data);

		return $next($data);
	}

	/**
	 * Warning if the Dropbox key does not exists.
	 *
	 * @param DiagnosticData[] $data
	 *
	 * @return void
	 */
	private function checkDropBoxKeyWarning(array &$data): void
	{
		$dropbox = Configs::getValueAsString('dropbox_key');
		if ($dropbox === '') {
			$data[] = DiagnosticData::warn('Dropbox import not working. dropbox_key is empty.', self::class);
			$data[] = DiagnosticData::info('To hide this Dropbox warning, set the dropbox_key to "disabled".', self::class);
		}
	}

	/**
	 * Sanity check of the config.
	 *
	 * @param DiagnosticData[] $return
	 */
	private function sanity(array &$return): void
	{
		$configs = Configs::all(['key', 'value', 'type_range']);

		foreach ($configs as $config) {
			$message = $config->sanity($config->value);
			if ($message !== '') {
				$return[] = DiagnosticData::error(str_replace('Error: ', '', $message), self::class);
			}
		}
	}
}
