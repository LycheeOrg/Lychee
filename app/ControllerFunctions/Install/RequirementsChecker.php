<?php

namespace App\ControllerFunctions\Install;

class RequirementsChecker
{
	/**
	 * Minimum PHP Version Supported (Override is in installer.php config file).
	 *
	 * @var _minPhpVersion
	 */
	private $_minPhpVersion = '7.2.0';

	/**
	 * Check for the server requirements.
	 *
	 * @param array $requirements
	 *
	 * @return array
	 */
	public function check(array $requirements)
	{
		$results = [];
		foreach ($requirements as $type => $requirement_) {
			switch ($type) {
				// check php requirements
				case 'php':
					foreach ($requirements[$type] as $requirement) {
						$results['requirements'][$type][$requirement] = true;
						if (!extension_loaded($requirement)) {
							// @codeCoverageIgnoreStart
							$results['requirements'][$type][$requirement]
								= false;
							$results['errors'] = true;
							// @codeCoverageIgnoreEnd
						}
					}

					if ($this->checkExec()) {
						$results['requirements'][$type]['Php exec() available'] = true;
					} else {
						// @codeCoverageIgnoreStart
						$results['requirements'][$type]['Php exec() not available (optional)'] = false;
						// @codeCoverageIgnoreEnd
					}

					break;
				// check apache requirements
				case 'apache':
					foreach ($requirements[$type] as $requirement) {
						// if function doesn't exist we can't check apache modules
						if (function_exists('apache_get_modules')) {
							$results['requirements'][$type][$requirement]
								= true;
							if (!in_array($requirement, apache_get_modules())) {
								// @codeCoverageIgnoreStart
								$results['requirements'][$type][$requirement]
									= false;
								$results['errors'] = true;
								// @codeCoverageIgnoreEnd
							}
						}
					}
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
	public function checkPHPversion(string $minPhpVersion = null)
	{
		$minVersionPhp = $minPhpVersion;
		$currentPhpVersion = $this->getPhpVersionInfo();
		$supported = false;
		if ($minPhpVersion == null) {
			$minVersionPhp = $this->getMinPhpVersion();
		}
		if (version_compare($currentPhpVersion['version'], $minVersionPhp)
			>= 0
		) {
			$supported = true;
		}
		$phpStatus = [
			'full' => $currentPhpVersion['full'],
			'current' => $currentPhpVersion['version'],
			'minimum' => $minVersionPhp,
			'supported' => $supported,
		];

		return $phpStatus;
	}

	/**
	 * Check if exec is enabled. This will allow us to execute the migration.
	 *
	 * @return bool
	 */
	public function checkExec()
	{
		$disabled = explode(',', ini_get('disable_functions'));

		return !in_array('exec', $disabled);
	}

	/**
	 * Get current Php version information.
	 *
	 * @return array
	 */
	private static function getPhpVersionInfo()
	{
		$currentVersionFull = PHP_VERSION;
		preg_match("#^\d+(\.\d+)*#", $currentVersionFull, $filtered);
		$currentVersion = $filtered[0];

		return [
			'full' => $currentVersionFull,
			'version' => $currentVersion,
		];
	}

	/**
	 * Get minimum PHP version ID.
	 *
	 * @return string _minPhpVersion
	 */
	protected function getMinPhpVersion()
	{
		return $this->_minPhpVersion;
	}
}