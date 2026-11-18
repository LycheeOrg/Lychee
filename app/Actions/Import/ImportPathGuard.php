<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Import;

use App\Enum\ImportPathStatus;
use Safe\Exceptions\FilesystemException;
use function Safe\realpath;

/**
 * Containment checks for import from server (Feature 115, ADR-115-01).
 *
 * A path is accepted for a restricted import root only when it is inside the
 * root lexically (no disk access) and its real path is inside the real root.
 * The lexical test runs first, so nothing outside the root is ever touched.
 */
final class ImportPathGuard
{
	/**
	 * Resolve `.`, `..` and repeated separators without touching the disk.
	 * A relative path stays relative (and is therefore never inside an absolute root).
	 */
	public static function normalise(string $path): string
	{
		$is_absolute = str_starts_with($path, '/');
		$segments = [];
		foreach (explode('/', $path) as $segment) {
			if ($segment === '' || $segment === '.') {
				continue;
			}
			if ($segment === '..') {
				array_pop($segments);
				continue;
			}
			$segments[] = $segment;
		}

		return ($is_absolute ? '/' : '') . implode('/', $segments);
	}

	/**
	 * Whether $path equals $root or is below it, compared with a trailing separator.
	 */
	public static function isWithin(string $path, string $root): bool
	{
		if ($root === '/') {
			return str_starts_with($path, '/');
		}

		return $path === $root || str_starts_with($path, $root . '/');
	}

	/**
	 * Import root of a `server_path`: `null` for full access (`/`),
	 * otherwise the real path of the folder (or its normalised path when it does not exist).
	 */
	public static function realRoot(?string $server_path): ?string
	{
		if ($server_path === null || $server_path === '/') {
			return null;
		}

		$normalised = self::normalise($server_path);

		return self::resolve($normalised) ?? $normalised;
	}

	/**
	 * Check $path against $root.
	 *
	 * @param string      $path path requested
	 * @param string|null $root import root, `null` when unrestricted
	 */
	public static function check(string $path, ?string $root): ImportPathStatus
	{
		if ($root === null) {
			return file_exists($path) ? ImportPathStatus::INSIDE : ImportPathStatus::MISSING;
		}

		$normalised_root = self::normalise($root);
		$normalised = self::normalise($path);
		if (!self::isWithin($normalised, $normalised_root)) {
			return ImportPathStatus::OUTSIDE;
		}

		$real_root = self::resolve($normalised_root);
		$real = self::resolve($normalised);
		if ($real_root === null || $real === null) {
			return ImportPathStatus::MISSING;
		}

		return self::isWithin($real, $real_root) ? ImportPathStatus::INSIDE : ImportPathStatus::OUTSIDE;
	}

	/**
	 * Real path of $path, or `null` when it does not exist.
	 */
	public static function resolve(string $path): ?string
	{
		try {
			return realpath($path);
		} catch (FilesystemException) {
			return null;
		}
	}
}
