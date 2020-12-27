<?php

namespace App\Contracts;

interface DiagnosticCheckInterface
{
	public function check(array &$errors): void;
}
