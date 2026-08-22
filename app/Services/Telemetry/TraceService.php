<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Telemetry;

use App\Contracts\Telemetry\TraceServiceInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\Span;

final class TraceService implements TraceServiceInterface
{
	public function addAttributeToCurrentSpan(string $name, mixed $value): void
	{
		Span::getCurrent()->setAttribute($name, $value);
	}

	public function addMemoryInfoToCurrentSpan(): void
	{
		$this->addAttributeToCurrentSpan('lychee.memory.current', memory_get_usage(true));
		$this->addAttributeToCurrentSpan('lychee.memory.peak', memory_get_peak_usage(true));
	}

	public function addEventToCurrentSpan(string $name, array $attributes = []): void
	{
		Span::getCurrent()->addEvent($name, $attributes);
	}

	public function addEventWithMemToCurrentSpan(string $eventName): void
	{
		$this->addEventToCurrentSpan($eventName, [
			'lychee.memory.current' => memory_get_usage(true),
			'lychee.memory.peak' => memory_get_peak_usage(true),
		]);
	}

	public function traceMethod(string $name, \Closure $callback): mixed
	{
		$tracer = Globals::tracerProvider()->getTracer('lychee');

		$span = $tracer
			->spanBuilder($name)
			->startSpan();

		if (!$span->isRecording()) {
			return $callback();
		}

		$before = memory_get_usage(true);

		$scope = $span->activate();

		try {
			$result = $callback();

			$span->setAttributes([
				'lychee.memory.before' => $before,
				'lychee.memory.after' => memory_get_usage(true),
				'lychee.memory.peak' => memory_get_peak_usage(true),
			]);

			return $result;
		} finally {
			$scope->detach();
			$span->end();
		}
	}
}

