<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\DTO;

use App\DTO\ArrayableDTO;
use App\Enum\UpdateStatus;

final class DiagnosticInfo extends ArrayableDTO
{
	/**
	 * @param string[]     $errors  list of error messages
	 * @param string[]     $infos   list of informational messages
	 * @param string[]     $configs list of configuration settings
	 * @param UpdateStatus $update  the update status
	 */
	public function __construct(
		public array $errors,
		public array $infos,
		public array $configs,
		public UpdateStatus $update,
	) {
	}
}