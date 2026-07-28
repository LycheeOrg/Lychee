<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO\Profiling;

/**
 * Result of {@see \App\Services\Profiling\PprofRenderer::render()} (FR-053-04).
 *
 * `$reason` matches TE-053-02's `binary_missing`|`process_error` values and
 * is only set when `$success` is false.
 */
final class PprofRenderResult
{
	private function __construct(
		public readonly bool $success,
		public readonly ?string $svg,
		public readonly ?string $reason,
		public readonly ?string $error_message,
	) {
	}

	public static function success(string $svg): self
	{
		return new self(true, $svg, null, null);
	}

	public static function binaryMissing(string $binary): self
	{
		return new self(false, null, 'binary_missing', sprintf("The '%s' binary was not found on this server.", $binary));
	}

	public static function processError(string $error_message): self
	{
		return new self(false, null, 'process_error', $error_message);
	}
}
