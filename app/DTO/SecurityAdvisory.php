<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * Immutable DTO representing a single GitHub Security Advisory that
 * affects the running Lychee version.
 */
class SecurityAdvisory
{
	public function __construct(
		public readonly ?string $cve_id,
		public readonly string $ghsa_id,
		public readonly string $summary,
		public readonly ?float $cvss_score,
		public readonly ?string $cvss_vector,
		public readonly string $affected_version_range,
	) {
	}
}
