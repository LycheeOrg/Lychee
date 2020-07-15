<?php

namespace App\ControllerFunctions\Diagnostics;

interface DiagnosticCheckInterface
{
	public function check(array &$errors): void;
}
