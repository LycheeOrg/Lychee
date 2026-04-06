<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Assets\Features;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Models\User;
use App\Services\SecurityAdvisoriesService;
use Illuminate\Support\Facades\Auth;

/**
 * Diagnostic pipe that reports known security vulnerabilities affecting the
 * currently installed Lychee version.
 *
 * Only runs when:
 *   - the `vulnerability-check` feature flag is enabled, and
 *   - the currently authenticated user is an administrator.
 *
 * Vulnerability data is never disclosed to non-admin users.
 */
class SecurityAdvisoriesCheck implements DiagnosticPipe
{
	public function __construct(
		private SecurityAdvisoriesService $service,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (Features::inactive('vulnerability-check')) {
			return $next($data);
		}

		/** @var User|null */
		$user = Auth::user();

		if ($user?->may_administrate !== true) {
			return $next($data);
		}

		$advisories = $this->service->getMatchingAdvisories();

		foreach ($advisories as $advisory) {
			$identifier = $advisory->cve_id ?? $advisory->ghsa_id;
			$score = $advisory->cvss_score !== null
				? number_format($advisory->cvss_score, 1)
				: '(no CVSS score)';

			$data[] = DiagnosticData::error(
				'Security vulnerability: ' . $identifier . ' (CVSS ' . $score . ')',
				self::class,
				[$advisory->summary],
			);
		}

		return $next($data);
	}
}
