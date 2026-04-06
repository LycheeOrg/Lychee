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

namespace Tests\Unit\Services;

use App\DTO\SecurityAdvisory;
use App\DTO\Version;
use App\Metadata\Json\AdvisoriesRequest;
use App\Metadata\Versions\InstalledVersion;
use App\Services\SecurityAdvisoriesService;
use App\Services\VersionRangeChecker;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;

/**
 * Unit tests for {@see SecurityAdvisoriesService}.
 *
 * The AdvisoriesRequest is mocked so no real HTTP calls are made.
 * Fixture JSON is decoded and injected directly.
 */
class SecurityAdvisoriesServiceTest extends AbstractTestCase
{
	/** @var AdvisoriesRequest|\Mockery\MockInterface */
	private AdvisoriesRequest $mock_request;

	/** @var InstalledVersion|\Mockery\MockInterface */
	private InstalledVersion $mock_version;

	private VersionRangeChecker $checker;

	/** @var array<int,object> fixture data decoded from JSON */
	private array $fixture_data;

	public function setUp(): void
	{
		parent::setUp();

		$this->mock_request = \Mockery::mock(AdvisoriesRequest::class);
		$this->mock_version = \Mockery::mock(InstalledVersion::class);
		$this->checker = new VersionRangeChecker();

		// Decode fixture JSON.
		$json = file_get_contents(base_path('tests/Fixtures/github-security-advisories.json'));
		$this->fixture_data = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

		Config::set('features.vulnerability-check', true);
	}

	public function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	private function makeService(): SecurityAdvisoriesService
	{
		return new SecurityAdvisoriesService(
			$this->mock_request,
			$this->checker,
			$this->mock_version,
		);
	}

	// ── feature disabled (S-032-01) ──────────────────────────────────────────

	public function testReturnsEmptyArrayWhenFeatureDisabled(): void
	{
		Config::set('features.vulnerability-check', false);

		// Request should never be called.
		$this->mock_request->shouldNotReceive('get_json');

		$service = $this->makeService();
		$this->assertSame([], $service->getMatchingAdvisories());
	}

	// ── fetch failure / empty response (S-032-05) ────────────────────────────

	public function testReturnsEmptyArrayOnNullResponse(): void
	{
		$this->mock_version->shouldReceive('getVersion')->andReturn(new Version(5, 0, 0));
		$this->mock_request->shouldReceive('get_json')->andReturn(null);

		$service = $this->makeService();
		$this->assertSame([], $service->getMatchingAdvisories());
	}

	public function testReturnsEmptyArrayOnEmptyArrayResponse(): void
	{
		$this->mock_version->shouldReceive('getVersion')->andReturn(new Version(5, 0, 0));
		$this->mock_request->shouldReceive('get_json')->andReturn([]);

		$service = $this->makeService();
		$this->assertSame([], $service->getMatchingAdvisories());
	}

	// ── no matching advisories (S-032-02) ────────────────────────────────────

	public function testReturnsEmptyArrayWhenNoAdvisoriesMatch(): void
	{
		// Version 1.0.0 — only the "< 1.0.0" advisory would match (CVE-2024-00002),
		// but that range is < 1.0.0, so 1.0.0 does NOT match.
		// The first and third advisories match ">= 4.0.0, < 99.0.0" but 1.0.0 < 4.0.0.
		$this->mock_version->shouldReceive('getVersion')->andReturn(new Version(1, 0, 0));
		$this->mock_request->shouldReceive('get_json')->andReturn($this->fixture_data);

		$service = $this->makeService();
		// Only CVE-2024-00002 (< 1.0.0) would match 0.x, not 1.0.0.
		$result = $service->getMatchingAdvisories();
		$this->assertSame([], $result);
	}

	// ── single matching advisory (S-032-03) ──────────────────────────────────

	public function testReturnsSingleMatchingAdvisory(): void
	{
		// Version 5.0.0 matches ">= 4.0.0, < 99.0.0" from fixture advisories 1 & 3,
		// but NOT "< 1.0.0" from advisory 2.
		$this->mock_version->shouldReceive('getVersion')->andReturn(new Version(5, 0, 0));
		$this->mock_request->shouldReceive('get_json')->andReturn(
			// Return only first advisory.
			[$this->fixture_data[0]],
		);

		$service = $this->makeService();
		$result = $service->getMatchingAdvisories();

		$this->assertCount(1, $result);
		$this->assertInstanceOf(SecurityAdvisory::class, $result[0]);
		$this->assertSame('CVE-2024-00001', $result[0]->cve_id);
		$this->assertSame('GHSA-1111-2222-3333', $result[0]->ghsa_id);
		$this->assertEqualsWithDelta(9.8, $result[0]->cvss_score, 0.01);
	}

	// ── multiple matching advisories (S-032-04) and sort order (Q-032-06) ────

	public function testReturnsMultipleMatchingAdvisoriesSortedByCvssDesc(): void
	{
		// Version 5.0.0 matches advisories 1 (CVSS 9.8) and 3 (null CVSS).
		$this->mock_version->shouldReceive('getVersion')->andReturn(new Version(5, 0, 0));
		$this->mock_request->shouldReceive('get_json')->andReturn($this->fixture_data);

		$service = $this->makeService();
		$result = $service->getMatchingAdvisories();

		// Advisories 1 (9.8) and 3 (null CVSS) match; advisory 2 (< 1.0.0) does not.
		$this->assertCount(2, $result);

		// First: highest CVSS score (9.8)
		$this->assertSame('CVE-2024-00001', $result[0]->cve_id);
		$this->assertEqualsWithDelta(9.8, $result[0]->cvss_score, 0.01);

		// Second: null CVSS → nulls last
		$this->assertNull($result[1]->cve_id);
		$this->assertNull($result[1]->cvss_score);
	}

	// ── deduplication by ghsa_id (Q-032-04) ──────────────────────────────────

	public function testDeduplicatesByGhsaId(): void
	{
		// Duplicate the first advisory item.
		$duplicated = [$this->fixture_data[0], $this->fixture_data[0]];

		$this->mock_version->shouldReceive('getVersion')->andReturn(new Version(5, 0, 0));
		$this->mock_request->shouldReceive('get_json')->andReturn($duplicated);

		$service = $this->makeService();
		$result = $service->getMatchingAdvisories();

		$this->assertCount(1, $result);
	}

	// ── advisory with null cve_id (Q-032-05) ─────────────────────────────────

	public function testHandlesAdvisoryWithNullCveId(): void
	{
		$this->mock_version->shouldReceive('getVersion')->andReturn(new Version(5, 0, 0));
		$this->mock_request->shouldReceive('get_json')->andReturn([$this->fixture_data[2]]);

		$service = $this->makeService();
		$result = $service->getMatchingAdvisories();

		$this->assertCount(1, $result);
		$this->assertNull($result[0]->cve_id);
		$this->assertSame('GHSA-7777-8888-9999', $result[0]->ghsa_id);
		$this->assertNull($result[0]->cvss_score);
	}

	// ── advisory with null cvss (Q-032-07) ───────────────────────────────────

	public function testHandlesAdvisoryWithNullCvss(): void
	{
		$this->mock_version->shouldReceive('getVersion')->andReturn(new Version(5, 0, 0));
		// third fixture advisory has cvss: null
		$this->mock_request->shouldReceive('get_json')->andReturn([$this->fixture_data[2]]);

		$service = $this->makeService();
		$result = $service->getMatchingAdvisories();

		$this->assertCount(1, $result);
		$this->assertNull($result[0]->cvss_score);
		$this->assertNull($result[0]->cvss_vector);
	}

	// ── version not determinable ─────────────────────────────────────────────

	public function testReturnsEmptyArrayWhenVersionThrows(): void
	{
		$this->mock_version->shouldReceive('getVersion')
			->andThrow(new \RuntimeException('DB not available'));

		// Request should not be called.
		$this->mock_request->shouldNotReceive('get_json');

		$service = $this->makeService();
		$this->assertSame([], $service->getMatchingAdvisories());
	}
}
