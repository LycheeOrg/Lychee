<?php

namespace App\Actions\Diagnostics;

interface DiagnosticCheckInterface
{
	public function check(array &$errors): void;
}
