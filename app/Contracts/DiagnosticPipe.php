<?php

namespace App\Contracts;

use App\DTO\DiagnosticData;

/**
 * Basic definition of a Diagnostic pipe.
 *
 * handle function takes as input the list of the previous errors/information
 * and return the updated list.
 */
interface DiagnosticPipe
{
	/**
	 * @param DiagnosticData[]                                   &$data
	 * @param \Closure(DiagnosticData[] $data): DiagnosticData[] $next
	 *
	 * @return DiagnosticData[]
	 */
	public function handle(array &$data, \Closure $next): array;
}
