<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\InstallUpdate;

use App\Facades\Helpers;
use function Safe\preg_match;

class RequirementsChecker
{
	/**
	 * Minimum PHP Version Supported.
	 */
	public const MIN_PHP_VERSION = '8.2.0';

	/**
	 * Check for the server requirements.
	 *
	 * @param array<string,array<int,string>> $requirements
	 *
	 * @return array{requirements:array<string,array<string,bool>>,errors:bool}
	 */
	public function check(array $requirements): array
	{
		$results = [
			'errors' => false,
			'requirements' => [],
		];
		foreach ($requirements as $type => $requirement_) {
			if ($type === 'php') {
				// check php requirements
				foreach ($requirement_ as $requirement) {
					$has_extension = extension_loaded($requirement);
					$results['requirements'][$type][$requirement] = $has_extension;
					// Note: Don't use the short-cut assignment `|=`;
					// it silently converts the type to integer, because
					// `|` is not the logical OR, but the bitwise OR.
					$results['errors'] = $results['errors'] || !$has_extension;
				}

				if (Helpers::isExecAvailable()) {
					$results['requirements'][$type]['Php exec() available'] = true;
				} else {
					$results['requirements'][$type]['Php exec() not available (optional)'] = false;
				}
			} elseif ($type === 'apache') {
				// check apache requirements
				foreach ($requirement_ as $requirement) {
					// if function doesn't exist we can't check apache modules
					$has_module = !function_exists('apache_get_modules') || in_array($requirement, apache_get_modules(), true);
					$results['requirements'][$type][$requirement] = $has_module;
					$results['errors'] = $results['errors'] || !$has_module;
				}
			}
		}

		return $results;
	}

	/**
	 * Check PHP version requirement.
	 *
	 * @param string|null $minPhpVersion
	 *
	 * @return array{full:string,current:string,minimum:string,supported:bool}
	 */
	public function checkPHPVersion(?string $min_php_version = null): array
	{
		$min_version_php = $min_php_version ?? self::MIN_PHP_VERSION;
		$current_php_version = self::getPhpVersionInfo();
		$supported = version_compare($current_php_version['version'], $min_version_php) >= 0;

		return [
			'full' => $current_php_version['full'],
			'current' => $current_php_version['version'],
			'minimum' => $min_version_php,
			'supported' => $supported,
		];
	}

	/**
	 * Get current Php version information.
	 *
	 * @return array{full:string,version:string}
	 */
	private static function getPhpVersionInfo(): array
	{
		$current_version_full = PHP_VERSION;
		preg_match('#^\d+(\.\d+)*#', $current_version_full, $filtered);
		$current_version = $filtered[0];

		return [
			'full' => $current_version_full,
			'version' => $current_version,
		];
	}
}
