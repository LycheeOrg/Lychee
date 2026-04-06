<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\Assets\Features;
use App\DTO\SecurityAdvisory;
use App\Metadata\Json\AdvisoriesRequest;
use App\Metadata\Versions\InstalledVersion;
use Illuminate\Support\Facades\Log;

/**
 * Fetches, caches, and filters GitHub Security Advisories for the running
 * Lychee version.
 *
 * Returned advisories are deduplicated by GHSA ID and sorted by CVSS score
 * descending (nulls last), then by CVE ID descending (nulls last).
 */
class SecurityAdvisoriesService
{
	public function __construct(
		private AdvisoriesRequest $request,
		private VersionRangeChecker $range_checker,
		private InstalledVersion $installed_version,
	) {
	}

	/**
	 * Return the list of published advisories that affect the running Lychee
	 * version.
	 *
	 * Returns an empty array when the feature is disabled, when the remote
	 * fetch fails, or when no advisories match the installed version.
	 *
	 * @return SecurityAdvisory[]
	 */
	public function getMatchingAdvisories(): array
	{
		if (Features::inactive('vulnerability-check')) {
			return [];
		}

		try {
			$version = $this->installed_version->getVersion();
		} catch (\Throwable $e) {
			Log::debug('SecurityAdvisories: unable to determine installed version — ' . $e->getMessage());

			return [];
		}

		$raw = $this->request->get_json(use_cache: true);

		if (!is_array($raw) || count($raw) === 0) {
			return [];
		}

		/** @var array<string,SecurityAdvisory> $seen keyed by ghsa_id for deduplication */
		$seen = [];

		foreach ($raw as $item) {
			if (!is_object($item)) {
				continue;
			}

			$ghsa_id = $item->ghsa_id ?? null;
			$cve_id = $item->cve_id ?? null;
			$summary = $item->summary ?? '';
			$cvss_score = $item->cvss->score ?? null;
			$cvss_vector = $item->cvss->vector_string ?? null;
			$vulnerabilities = $item->vulnerabilities ?? [];

			if ($ghsa_id === null) {
				continue;
			}

			// Skip if already seen (deduplication by ghsa_id).
			if (isset($seen[$ghsa_id])) {
				continue;
			}

			if (!is_array($vulnerabilities) || count($vulnerabilities) === 0) {
				continue;
			}

			foreach ($vulnerabilities as $vuln) {
				if (!is_object($vuln)) {
					continue;
				}

				$range = $vuln->vulnerable_version_range ?? '';
				$patched = $vuln->patched_versions ?? '';

				// Check if version is in the vulnerable range
				if ($this->range_checker->matches($version, (string) $range)) {
					// If patched versions are defined, check if we're patched
					if ($patched !== '' && $this->range_checker->matches($version, (string) $patched)) {
						// Version is patched, not vulnerable — skip this advisory
						continue;
					}

					// Version is vulnerable and not patched — include this advisory
					$seen[$ghsa_id] = new SecurityAdvisory(
						cve_id: $cve_id,
						ghsa_id: $ghsa_id,
						summary: $summary,
						cvss_score: $cvss_score !== null ? (float) $cvss_score : null,
						cvss_vector: $cvss_vector,
						affected_version_range: (string) $range,
					);
					break; // one match per advisory is enough
				}
			}
		}

		$advisories = array_values($seen);

		// Sort by CVSS score DESC (nulls last), then CVE ID DESC (nulls last).
		usort($advisories, function (SecurityAdvisory $a, SecurityAdvisory $b): int {
			$score_a = $a->cvss_score;
			$score_b = $b->cvss_score;

			if ($score_a !== $score_b) {
				if ($score_a === null) {
					return 1; // nulls last
				}
				if ($score_b === null) {
					return -1; // nulls last
				}

				return $score_b <=> $score_a; // DESC
			}

			$cve_a = $a->cve_id;
			$cve_b = $b->cve_id;

			if ($cve_a === null && $cve_b === null) {
				return 0;
			}
			if ($cve_a === null) {
				return 1; // nulls last
			}
			if ($cve_b === null) {
				return -1; // nulls last
			}

			return strcmp($cve_b, $cve_a); // DESC
		});

		return $advisories;
	}
}
