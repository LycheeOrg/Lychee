<?php

namespace App\Contracts;

interface DiagnosticCheckInterface
{
	/**
	 * @param string[] $errors list of error messages
	 *
	 * @return void
	 */
	public function check(array &$errors): void;
}
