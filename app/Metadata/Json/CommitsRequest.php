<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Json;

use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Config;

class CommitsRequest extends JsonRequestFunctions
{
	/**
	 * we just override the constructor,
	 * The rest is handled directly by the parent class.
	 */
	public function __construct()
	{
		$config_manager = app(ConfigManager::class);

		parent::__construct(
			Config::get('urls.update.git.commits'),
			$config_manager->getValueAsInt('update_check_every_days')
		);
	}
}
