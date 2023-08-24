<?php

namespace App\Actions\Diagnostics;

use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Configs;
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
			return ['Error: migration has not been run yet.'];
		}

		// Load settings
		$settings = Configs::query()
			->where('confidentiality', '<=', 2)
			->select(['key', 'value'])
			->orderBy('id', 'ASC')
			->get();

		return $settings->map(function (Configs $setting) {
			if (is_null($setting->value)) {
				return 'Error: ' . $setting->key . ' has a NULL value!';
			} else {
				return Diagnostics::line($setting->key . ':', $setting->value);
			}
		})->all();
	}
}
