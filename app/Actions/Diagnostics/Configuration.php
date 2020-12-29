<?php

namespace App\Actions\Diagnostics;

use App\ModelFunctions\ConfigFunctions;
use Illuminate\Database\QueryException;

class Configuration
{
	use Line;

	/** @var ConfigFunctions */
	private $configFunctions;

	public function __construct(ConfigFunctions $configFunctions)
	{
		$this->configFunctions = $configFunctions;
	}

	/**
	 * Return the config pieces of information of the Lychee installation.
	 * Note that some information such as password and username are hidden.
	 *
	 * @return array
	 */
	public function get(): array
	{
		// Declare
		$configs = [];

		try {
			// Load settings
			$settings = $this->configFunctions->min_info();
			foreach ($settings as $key => $value) {
				if (!is_array($value) && !is_null($value)) {
					$configs[] = $this->line($key . ':', $value);
				} elseif (is_null($value)) {
					$configs[] = 'Error: ' . $key . ' has a NULL value!';
				}
			}
		} catch (QueryException $e) {
			$configs[] = 'Error: ' . $e->getMessage();
		}

		return $configs;
	}
}
