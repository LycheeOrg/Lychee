<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

declare(strict_types=1);

namespace App\Services\Zip;

use RuntimeException;

/**
 * Thrown by {@see SafeZipExtractor} specifically when an archive is rejected
 * for exceeding one of the configured zip-bomb protection limits (declared
 * or real uncompressed size, entry count, compression ratio).
 *
 * Kept distinct from the plainer {@see RuntimeException} thrown for I/O
 * failures and zip-slip path traversal, so callers can tell a genuine
 * zip-bomb detection apart from other extraction failures.
 */
final class ZipBombDetectedException extends RuntimeException
{
}
