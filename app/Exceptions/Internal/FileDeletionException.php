<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class FileDeletionException extends LycheeDomainException
{
	public function __construct(string $storage, string $path)
	{
		parent::__construct(sprintf('Storage::delete (%s) failed: %s', $storage, $path));
	}
}