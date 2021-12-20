<?php

namespace App\Actions\Diagnostics;

use App\Factories\DiagnosticsChecksFactory;

class Errors extends Diagnostics
{
	private DiagnosticsChecksFactory $diagnosticsChecksFactory;

	public function __construct(DiagnosticsChecksFactory $diagnosticsChecksFactory)
	{
		$this->diagnosticsChecksFactory = $diagnosticsChecksFactory;
	}

	/**
	 * Return the list of error which are currently breaking Lychee.
	 *
	 * @return string[] array of messages
	 */
	public function get(): array
	{
		// Declare
		$errors = [];

		// @codeCoverageIgnoreStart

		$checks = $this->diagnosticsChecksFactory->makeAll();

		foreach ($checks as $check) {
			$check->check($errors);
		}
		// @codeCoverageIgnoreEnd

		return $errors;
	}
}
