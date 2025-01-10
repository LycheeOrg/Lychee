<?php

namespace App\Contracts;

/**
 * Basic definition of a Diagnostic pipe.
 *
 * handle function takes as input the list of the previous errors/information
 * and return the updated list.
 */
interface DiagnosticStringPipe
{
	/**
	 * @param string[]                           &$data
	 * @param \Closure(string[] $data): string[] $next
	 *
	 * @return string[]
	 */
	public function handle(array &$data, \Closure $next): array;
}
