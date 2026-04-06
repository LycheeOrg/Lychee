<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\DTO\SecurityAdvisory;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * API resource for a single security advisory that affects the running
 * Lychee version.
 */
#[TypeScript()]
class SecurityAdvisoryResource extends Data
{
	public function __construct(
		public readonly ?string $cve_id,
		public readonly string $ghsa_id,
		public readonly string $summary,
		public readonly ?float $cvss_score,
		public readonly ?string $cvss_vector,
	) {
	}

	/**
	 * Build a resource from a SecurityAdvisory DTO.
	 *
	 * @param SecurityAdvisory $advisory
	 *
	 * @return self
	 */
	public static function fromAdvisory(SecurityAdvisory $advisory): self
	{
		return new self(
			cve_id: $advisory->cve_id,
			ghsa_id: $advisory->ghsa_id,
			summary: $advisory->summary,
			cvss_score: $advisory->cvss_score,
			cvss_vector: $advisory->cvss_vector,
		);
	}
}
