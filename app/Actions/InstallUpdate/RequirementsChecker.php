<?php

namespace App\Actions\InstallUpdate;

use App\Facades\Helpers;
use function Safe\preg_match;

class RequirementsChecker
{
	/**
	 * Minimum PHP Version Supported.
	 */
	public const MIN_PHP_VERSION = '8.0.0';

	/**
	 * Check for the server requirements.
	 *
	 * @param array $requirements
	 *
	 * @return array
	 */
	public function check(array $requirements): array
	{
		$results = [];
		$results['errors'] = false;
		foreach ($requirements as $type => $requirement_) {
			switch ($type) {
				// check php requirements
				case 'php':
					foreach ($requirement_ as $requirement) {
						$hasExtension = extension_loaded($requirement);
						$results['requirements'][$type][$requirement] = $hasExtension;
						// Note: Don't use the short-cut assignment `|=`;
						// it silently converts the type to integer, because
						// `|` is not the logical OR, but the bitwise OR.
						$results['errors'] = $results['errors'] || !$hasExtension;
					}

					if (Helpers::isExecAvailable()) {
						$results['requirements'][$type]['Php exec() available'] = true;
					} else {
						$results['requirements'][$type]['Php exec() not available (optional)'] = false;
					}

					break;
					// check apache requirements
				case 'apache':
					foreach ($requirement_ as $requirement) {
						// if function doesn't exist we can't check apache modules
						$hasModule = !function_exists('apache_get_modules') || in_array($requirement, apache_get_modules(), true);
						$results['requirements'][$type][$requirement] = $hasModule;
						$results['errors'] = $results['errors'] || !$hasModule;
					}
					break;
				default:
					break;
			}
		}

		return $results;
	}

	/**
	 * Check PHP version requirement.
	 *
	 * @param string|null $minPhpVersion
	 *
	 * @return array
	 */
	public function checkPHPVersion(?string $minPhpVersion = null): array
	{
		$minVersionPhp = $minPhpVersion ?? self::MIN_PHP_VERSION;
		$currentPhpVersion = self::getPhpVersionInfo();
		$supported = version_compare($currentPhpVersion['version'], $minVersionPhp) >= 0;

		return [
			'full' => $currentPhpVersion['full'],
			'current' => $currentPhpVersion['version'],
			'minimum' => $minVersionPhp,
			'supported' => $supported,
		];
	}

	/**
	 * Get current Php version information.
	 *
	 * @return array
	 */
	private static function getPhpVersionInfo(): array
	{
		$currentVersionFull = PHP_VERSION;
		preg_match('#^\d+(\.\d+)*#', $currentVersionFull, $filtered);
		$currentVersion = $filtered[0];

		return [
			'full' => $currentVersionFull,
			'version' => $currentVersion,
		];
	}
}
