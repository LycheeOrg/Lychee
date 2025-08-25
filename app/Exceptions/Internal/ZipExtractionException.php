<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions\Internal;

class ZipExtractionException extends LycheeDomainException
{
	public function __construct(string $message)
	{
		parent::__construct($message);
	}

	public static function fromTo(string $path, string $to): self
	{
		return new self(sprintf('Could not extract %s to %s', $path, $to));
	}
}
