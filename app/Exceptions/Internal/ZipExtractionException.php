<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class ZipExtractionException extends LycheeDomainException
{
	public function __construct(string $path, string $to)
	{
		parent::__construct(sprintf('Could not extract %s to %s', $path, $to));
	}
}
