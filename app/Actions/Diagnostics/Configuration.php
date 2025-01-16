<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics;

use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Configs;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class Configuration
{
	/**
	 * Return the config pieces of information of the Lychee installation.
	 * Note that some information such as password and username are hidden.
	 *
	 * @return array<int,string> array of messages
	 *
	 * @throws QueryBuilderException
	 */
	public function get(): array
	{
		if (!Schema::hasTable('configs')) {
			// @codeCoverageIgnoreStart
			return ['Error: migration has not been run yet.'];
			// @codeCoverageIgnoreEnd
		}

		// Load settings
		$settings = Schema::hasColumn('configs', 'is_secret') ? $this->withIsSecret() : $this->withConfidentiality();

		return $settings->map(function (Configs $setting) {
			if (is_null($setting->value)) {
				return 'Error: ' . $setting->key . ' has a NULL value!';
			} else {
				return Diagnostics::line($setting->key . ':', $setting->value);
			}
		})->all();
	}

	/**
	 * This is a fail safe (legacy) in case the migration 2024_04_09_121410 has not been applied.
	 *
	 * @return Collection<int,Configs>
	 *
	 * @throws QueryBuilderException
	 */
	private function withConfidentiality()
	{
		return Configs::query()
			->where('confidentiality', '<=', 2)
			->select(['key', 'value'])
			->orderBy('id', 'ASC')
			->get();
	}

	/**
	 * Normal code path.
	 *
	 * @return Collection<int,Configs>
	 *
	 * @throws QueryBuilderException
	 */
	private function withIsSecret()
	{
		return Configs::query()
			->where('is_secret', '=', false)
			->select(['key', 'value'])
			->orderBy('id', 'ASC')
			->get();
	}
}
