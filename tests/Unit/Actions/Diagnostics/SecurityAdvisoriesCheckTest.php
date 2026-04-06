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

namespace Tests\Unit\Actions\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\SecurityAdvisoriesCheck;
use App\DTO\DiagnosticData;
use App\DTO\SecurityAdvisory;
use App\Enum\MessageType;
use App\Models\User;
use App\Services\SecurityAdvisoriesService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\AbstractTestCase;

/**
 * Unit tests for {@see SecurityAdvisoriesCheck}.
 */
class SecurityAdvisoriesCheckTest extends AbstractTestCase
{
	/** @var SecurityAdvisoriesService|\Mockery\MockInterface */
	private SecurityAdvisoriesService $mock_service;

	/** @var \Closure */
	private \Closure $next;

	protected function setUp(): void
	{
		parent::setUp();

		$this->mock_service = \Mockery::mock(SecurityAdvisoriesService::class);
		$this->next = fn (array $data): array => $data;

		Config::set('features.vulnerability-check', true);
	}

	public function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	private function makeCheck(): SecurityAdvisoriesCheck
	{
		return new SecurityAdvisoriesCheck($this->mock_service);
	}

	private function makeAdmin(): User
	{
		$user = \Mockery::mock(User::class)->makePartial();
		$user->may_administrate = true;

		return $user;
	}

	private function makeNonAdmin(): User
	{
		$user = \Mockery::mock(User::class)->makePartial();
		$user->may_administrate = false;

		return $user;
	}

	// ── feature disabled (S-032-01) ──────────────────────────────────────────

	public function testNoEntriesWhenFeatureDisabled(): void
	{
		Config::set('features.vulnerability-check', false);

		$this->mock_service->shouldNotReceive('getMatchingAdvisories');

		$data = [];
		$result = $this->makeCheck()->handle($data, $this->next);
		$this->assertSame([], $result);
	}

	// ── non-admin user (NFR-032-01) ───────────────────────────────────────────

	public function testNoEntriesWhenUserIsNotAdmin(): void
	{
		Auth::shouldReceive('user')->andReturn($this->makeNonAdmin());
		$this->mock_service->shouldNotReceive('getMatchingAdvisories');

		$data = [];
		$result = $this->makeCheck()->handle($data, $this->next);
		$this->assertSame([], $result);
	}

	public function testNoEntriesWhenUnauthenticated(): void
	{
		Auth::shouldReceive('user')->andReturn(null);
		$this->mock_service->shouldNotReceive('getMatchingAdvisories');

		$data = [];
		$result = $this->makeCheck()->handle($data, $this->next);
		$this->assertSame([], $result);
	}

	// ── no advisories (S-032-02) ─────────────────────────────────────────────

	public function testNoEntriesWhenServiceReturnsEmpty(): void
	{
		Auth::shouldReceive('user')->andReturn($this->makeAdmin());
		$this->mock_service->shouldReceive('getMatchingAdvisories')->andReturn([]);

		$data = [];
		$result = $this->makeCheck()->handle($data, $this->next);
		$this->assertSame([], $result);
	}

	// ── single advisory with CVE ID and CVSS score (S-032-03) ────────────────

	public function testAddsErrorEntryForSingleAdvisory(): void
	{
		Auth::shouldReceive('user')->andReturn($this->makeAdmin());

		$advisory = new SecurityAdvisory(
			cve_id: 'CVE-2024-00001',
			ghsa_id: 'GHSA-1111-2222-3333',
			summary: 'Remote code execution',
			cvss_score: 9.8,
			cvss_vector: null,
			affected_version_range: '>= 5.0.0',
		);

		$this->mock_service->shouldReceive('getMatchingAdvisories')->andReturn([$advisory]);

		$data = [];
		$result = $this->makeCheck()->handle($data, $this->next);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(DiagnosticData::class, $result[0]);
		$this->assertSame(MessageType::ERROR, $result[0]->type);
		$this->assertSame('Security vulnerability: CVE-2024-00001 (CVSS 9.8)', $result[0]->message);
		$this->assertSame(['Remote code execution'], $result[0]->details);
	}

	// ── advisory falls back to GHSA ID when cve_id is null (Q-032-05) ────────

	public function testUsesGhsaIdWhenCveIdIsNull(): void
	{
		Auth::shouldReceive('user')->andReturn($this->makeAdmin());

		$advisory = new SecurityAdvisory(
			cve_id: null,
			ghsa_id: 'GHSA-7777-8888-9999',
			summary: 'Advisory without CVE',
			cvss_score: 5.0,
			cvss_vector: null,
			affected_version_range: '>= 4.0.0',
		);

		$this->mock_service->shouldReceive('getMatchingAdvisories')->andReturn([$advisory]);

		$data = [];
		$result = $this->makeCheck()->handle($data, $this->next);

		$this->assertCount(1, $result);
		$this->assertStringContainsString('GHSA-7777-8888-9999', $result[0]->message);
		$this->assertStringNotContainsString('null', $result[0]->message);
	}

	// ── null CVSS score displays "(no CVSS score)" (Q-032-07) ────────────────

	public function testFormatsNullCvssScore(): void
	{
		Auth::shouldReceive('user')->andReturn($this->makeAdmin());

		$advisory = new SecurityAdvisory(
			cve_id: null,
			ghsa_id: 'GHSA-7777-8888-9999',
			summary: 'Advisory without CVSS',
			cvss_score: null,
			cvss_vector: null,
			affected_version_range: '>= 4.0.0',
		);

		$this->mock_service->shouldReceive('getMatchingAdvisories')->andReturn([$advisory]);

		$data = [];
		$result = $this->makeCheck()->handle($data, $this->next);

		$this->assertCount(1, $result);
		$this->assertStringContainsString('(no CVSS score)', $result[0]->message);
	}

	// ── two advisories → two error entries in order (S-032-04) ───────────────

	public function testAddsOneErrorEntryPerAdvisory(): void
	{
		Auth::shouldReceive('user')->andReturn($this->makeAdmin());

		$advisories = [
			new SecurityAdvisory('CVE-2024-00001', 'GHSA-1111-2222-3333', 'RCE', 9.8, null, '>= 5.0.0'),
			new SecurityAdvisory('CVE-2024-00002', 'GHSA-4444-5555-6666', 'XSS', 6.1, null, '>= 5.0.0'),
		];

		$this->mock_service->shouldReceive('getMatchingAdvisories')->andReturn($advisories);

		$data = [];
		$result = $this->makeCheck()->handle($data, $this->next);

		$this->assertCount(2, $result);
		$this->assertSame(MessageType::ERROR, $result[0]->type);
		$this->assertSame(MessageType::ERROR, $result[1]->type);
		$this->assertStringContainsString('CVE-2024-00001', $result[0]->message);
		$this->assertStringContainsString('CVE-2024-00002', $result[1]->message);
	}

	// ── CVSS score formatted to 1 decimal (Q-032-07) ─────────────────────────

	public function testCvssScoreFormattedToOneDecimal(): void
	{
		Auth::shouldReceive('user')->andReturn($this->makeAdmin());

		$advisory = new SecurityAdvisory('CVE-2024-00001', 'GHSA-1111', 'Test', 9.0, null, '>= 5.0.0');
		$this->mock_service->shouldReceive('getMatchingAdvisories')->andReturn([$advisory]);

		$data = [];
		$result = $this->makeCheck()->handle($data, $this->next);

		$this->assertCount(1, $result);
		$this->assertStringContainsString('CVSS 9.0', $result[0]->message);
	}
}
