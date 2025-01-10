<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts;

/**
 * Basic definition of a Diagnostic pipe.
 *
 * handle function takes as input the list of the previous errors/information
 * and return the updated list.
 */
interface DiagnosticPipe
{
	/**
	 * @param array<int,string>                                    &$data
	 * @param \Closure(array<int,string> $data): array<int,string> $next
	 *
	 * @return array<int,string>
	 */
	public function handle(array &$data, \Closure $next): array;
}