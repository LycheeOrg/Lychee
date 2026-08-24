<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Services\Telemetry\TraceService;

abstract class AbstractStandalonePipe implements StandalonePipe
{
	/**
	 * @param StandaloneDTO                                 $state
	 * @param \Closure(StandaloneDTO $state): StandaloneDTO $next
	 *
	 * @return StandaloneDTO
	 */
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		$trace = app(TraceService::class);

		return $trace->traceMethod($this->getSpanName(), fn () => $this->execute($state, $next));
	}

	abstract protected function getSpanName(): string;

	abstract protected function execute(StandaloneDTO $state, \Closure $next): StandaloneDTO;
}
