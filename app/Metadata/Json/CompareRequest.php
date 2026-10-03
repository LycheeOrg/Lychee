<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\Json;

use App\Metadata\Versions\GitHubVersion;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Config;

/**
 * Compare a local commit with the remote master branch.
 *
 * `ahead_by` in the response is the number of commits master has that the local commit does not.
 * We only ask for one commit per page: the commit list is irrelevant to us, and on
 * non-final pages GitHub omits the (potentially large) list of changed files.
 */
class CompareRequest extends JsonRequestFunctions
{
	public function __construct(string $local_sha)
	{
		$config_manager = app(ConfigManager::class);

		parent::__construct(
			Config::get('urls.update.git.compare') . '/' . $local_sha . '...' . GitHubVersion::MASTER . '?per_page=1',
			$config_manager->getValueAsInt('update_check_every_days')
		);
	}
}
