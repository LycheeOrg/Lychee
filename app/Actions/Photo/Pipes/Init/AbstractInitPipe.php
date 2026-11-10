<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Init;

use App\Contracts\PhotoCreate\InitPipe;
use App\DTO\PhotoCreate\InitDTO;
use App\Services\Telemetry\TraceService;

abstract class AbstractInitPipe implements InitPipe
{
	/**
	 * @param InitDTO                           $state
	 * @param \Closure(InitDTO $state): InitDTO $next
	 *
	 * @return InitDTO
	 */
	public function handle(InitDTO $state, \Closure $next): InitDTO
	{
		$trace = app(TraceService::class);

		return $trace->traceMethod($this->getSpanName(), fn () => $this->execute($state, $next));
	}

	abstract protected function getSpanName(): string;

	abstract protected function execute(InitDTO $state, \Closure $next): InitDTO;
}
