<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts;

use App\DTO\DiagnosticDTO;

/**
 * Basic definition of a Diagnostic pipe.
 *
 * handle function takes as input the list of the previous errors/information
 * and return the updated list.
 */
interface DiagnosticPipe
{
	/**
	 * @param DiagnosticDTO                                &$data
	 * @param \Closure(DiagnosticDTO $data): DiagnosticDTO $next
	 *
	 * @return DiagnosticDTO
	 */
	public function handle(DiagnosticDTO &$data, \Closure $next): DiagnosticDTO;
}
