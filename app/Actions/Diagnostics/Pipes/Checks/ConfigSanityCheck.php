<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\DTO\DiagnosticDTO;
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
	public function handle(DiagnosticDTO &$data, \Closure $next): DiagnosticDTO
	{
		if (!Schema::hasTable('configs')) {
			// @codeCoverageIgnoreStart
			return $next($data);
			// @codeCoverageIgnoreEnd
		}

		$this->sanity($data);
		$this->checkDropBoxKeyWarning($data);

		return $next($data);
	}

	/**
	 * Warning if the Dropbox key does not exists.
	 *
	 * @param DiagnosticDTO $data
	 *
	 * @return void
	 */
	private function checkDropBoxKeyWarning(DiagnosticDTO &$data): void
	{
		$dropbox = $data->config_manager->getValueAsString('dropbox_key');
		if ($dropbox === '') {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('Dropbox import not working. dropbox_key is empty.', self::class);
			$data[] = DiagnosticData::info('To hide this Dropbox warning, set the dropbox_key to "disabled".', self::class);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Sanity check of the config.
	 *
	 * @param DiagnosticDTO $return
	 */
	private function sanity(DiagnosticDTO &$return): void
	{
		$configs = Configs::all(['key', 'value', 'type_range']);

		foreach ($configs as $config) {
			$message = $config->sanity($config->value);
			if ($message !== '') {
				// @codeCoverageIgnoreStart
				$return[] = DiagnosticData::error(str_replace('Error: ', '', $message), self::class);
				// @codeCoverageIgnoreEnd
			}
		}
	}
}
