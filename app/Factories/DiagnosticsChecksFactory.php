<?php

namespace App\Factories;

use App\Contracts\DiagnosticCheckInterface;
use function Safe\scandir;

class DiagnosticsChecksFactory
{
	/** @var string[] */
	public array $checks = [];

	public function __construct()
	{
		$checks_namespace = 'App\Actions\Diagnostics\Checks';
		$list_checks = scandir(__DIR__ . '/../Actions/Diagnostics/Checks');

		for ($i = 0; $i < count($list_checks); $i++) {
			$class_candidate = $checks_namespace . '\\' . substr($list_checks[$i], 0, -4);

			if (is_subclass_of($class_candidate, DiagnosticCheckInterface::class)) {
				$this->checks[] = $class_candidate;
			}
		}
	}

	/**
	 * @return DiagnosticCheckInterface[]
	 */
	public function makeAll(): array
	{
		$checks_ret = [];

		foreach ($this->checks as $check) {
			$checks_ret[] = resolve($check); // take care of dependency injection <3
		}

		return $checks_ret;
	}
}
