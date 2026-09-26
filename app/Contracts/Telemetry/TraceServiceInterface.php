<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Telemetry;

interface TraceServiceInterface
{
	public function addAttributeToCurrentSpan(string $name, mixed $value): void;

	public function addEventToCurrentSpan(string $name, array $attributes = []): void;

	public function addEventWithMemToCurrentSpan(string $eventName): void;
}
