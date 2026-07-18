<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

declare(strict_types=1);

namespace App\Exceptions\Internal;

use App\Contracts\Exceptions\InternalLycheeException;

/**
 * Thrown by {@see \App\Services\Zip\SafeZipExtractor} specifically when an archive is rejected
 * for exceeding one of the configured zip-bomb protection limits (declared
 * or real uncompressed size, entry count, compression ratio).
 *
 * Kept distinct from the plainer {@see \RuntimeException} thrown for I/O
 * failures and zip-slip path traversal, so callers can tell a genuine
 * zip-bomb detection apart from other extraction failures.
 *
 * This is an internal signal only: it is always caught and translated
 * into a {@see \App\Exceptions\ZipInvalidException} before reaching the user.
 */
final class ZipBombDetectedException extends \RuntimeException implements InternalLycheeException
{
	public function __construct(string $msg, ?\Throwable $previous = null)
	{
		parent::__construct($msg, 0, $previous);
	}
}
