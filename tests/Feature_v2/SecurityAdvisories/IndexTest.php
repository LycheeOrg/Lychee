<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\SecurityAdvisories;

use App\DTO\SecurityAdvisory;
use App\Services\SecurityAdvisoriesService;
use Illuminate\Support\Facades\Config;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature tests for GET /Security/Advisories.
 */
class IndexTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		Config::set('features.vulnerability-check', true);
	}

	// ── authentication / authorization ────────────────────────────────────────

	public function testUnauthenticatedReceives401(): void
	{
		$response = $this->getJson('Security/Advisories');
		$this->assertUnauthorized($response);
	}

	public function testNonAdminReceives403(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Security/Advisories');
		$this->assertForbidden($response);
	}

	// ── feature disabled (S-032-01) ──────────────────────────────────────────

	public function testAdminReceivesEmptyArrayWhenFeatureDisabled(): void
	{
		Config::set('features.vulnerability-check', false);

		$response = $this->actingAs($this->admin)->getJson('Security/Advisories');
		$this->assertOk($response);
		$response->assertExactJson([]);
	}

	// ── no matching advisories (S-032-02) ────────────────────────────────────

	public function testAdminReceivesEmptyArrayWhenNoAdvisoriesMatch(): void
	{
		$this->instance(
			SecurityAdvisoriesService::class,
			new class() extends SecurityAdvisoriesService {
				public function __construct()
				{
				}

				public function getMatchingAdvisories(): array
				{
					return [];
				}
			},
		);

		$response = $this->actingAs($this->admin)->getJson('Security/Advisories');
		$this->assertOk($response);
		$response->assertExactJson([]);
	}

	// ── matching advisories (S-032-03) ───────────────────────────────────────

	public function testAdminReceivesMatchingAdvisoryList(): void
	{
		$advisory = new SecurityAdvisory(
			cve_id: 'CVE-2024-00001',
			ghsa_id: 'GHSA-1111-2222-3333',
			summary: 'Remote code execution',
			cvss_score: 9.8,
			cvss_vector: 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
			affected_version_range: '>= 5.0.0',
		);

		$this->instance(
			SecurityAdvisoriesService::class,
			new class($advisory) extends SecurityAdvisoriesService {
				public function __construct(private SecurityAdvisory $advisory)
				{
				}

				public function getMatchingAdvisories(): array
				{
					return [$this->advisory];
				}
			},
		);

		$response = $this->actingAs($this->admin)->getJson('Security/Advisories');
		$this->assertOk($response);

		$data = $response->json();
		$this->assertCount(1, $data);
		$this->assertSame('CVE-2024-00001', $data[0]['cve_id']);
		$this->assertSame('GHSA-1111-2222-3333', $data[0]['ghsa_id']);
		$this->assertSame('Remote code execution', $data[0]['summary']);
		$this->assertEqualsWithDelta(9.8, $data[0]['cvss_score'], 0.01);
		$this->assertSame('CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H', $data[0]['cvss_vector']);
	}

	// ── response structure ────────────────────────────────────────────────────

	public function testAdvisoryResourceStructure(): void
	{
		$advisory = new SecurityAdvisory(
			cve_id: 'CVE-2024-00001',
			ghsa_id: 'GHSA-1111-2222-3333',
			summary: 'Test',
			cvss_score: 9.8,
			cvss_vector: null,
			affected_version_range: '>= 5.0.0',
		);

		$this->instance(
			SecurityAdvisoriesService::class,
			new class($advisory) extends SecurityAdvisoriesService {
				public function __construct(private SecurityAdvisory $advisory)
				{
				}

				public function getMatchingAdvisories(): array
				{
					return [$this->advisory];
				}
			},
		);

		$response = $this->actingAs($this->admin)->getJson('Security/Advisories');
		$this->assertOk($response);

		$response->assertJsonStructure([
			'*' => ['cve_id', 'ghsa_id', 'summary', 'cvss_score', 'cvss_vector'],
		]);
	}
}
