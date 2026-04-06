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

use App\DTO\Version;
use App\Services\VersionRangeChecker;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

/**
 * Unit tests for {@see VersionRangeChecker}.
 *
 * Covers all six operators and comma-separated multi-constraint ranges.
 */
class VersionRangeCheckerTest extends AbstractTestCase
{
	private VersionRangeChecker $checker;

	public function setUp(): void
	{
		parent::setUp();
		$this->checker = new VersionRangeChecker();
	}

	// ── empty / null range ───────────────────────────────────────────────────

	public function testEmptyRangeMatchesAllVersions(): void
	{
		$v = new Version(5, 0, 0);
		$this->assertTrue($this->checker->matches($v, ''));
		$this->assertTrue($this->checker->matches($v, '   '));
	}

	// ── >= operator ─────────────────────────────────────────────────────────

	public function testGreaterThanOrEqualMatchesEqualVersion(): void
	{
		$v = new Version(5, 1, 2);
		$this->assertTrue($this->checker->matches($v, '>= 5.1.2'));
	}

	public function testGreaterThanOrEqualMatchesHigherVersion(): void
	{
		$v = new Version(5, 2, 0);
		$this->assertTrue($this->checker->matches($v, '>= 5.1.2'));
	}

	public function testGreaterThanOrEqualDoesNotMatchLowerVersion(): void
	{
		$v = new Version(5, 1, 1);
		$this->assertFalse($this->checker->matches($v, '>= 5.1.2'));
	}

	// ── <= operator ─────────────────────────────────────────────────────────

	public function testLessThanOrEqualMatchesEqualVersion(): void
	{
		$v = new Version(5, 1, 2);
		$this->assertTrue($this->checker->matches($v, '<= 5.1.2'));
	}

	public function testLessThanOrEqualMatchesLowerVersion(): void
	{
		$v = new Version(5, 1, 1);
		$this->assertTrue($this->checker->matches($v, '<= 5.1.2'));
	}

	public function testLessThanOrEqualDoesNotMatchHigherVersion(): void
	{
		$v = new Version(5, 2, 0);
		$this->assertFalse($this->checker->matches($v, '<= 5.1.2'));
	}

	// ── > operator ──────────────────────────────────────────────────────────

	public function testGreaterThanMatchesHigherVersion(): void
	{
		$v = new Version(5, 2, 0);
		$this->assertTrue($this->checker->matches($v, '> 5.1.2'));
	}

	public function testGreaterThanDoesNotMatchEqualVersion(): void
	{
		$v = new Version(5, 1, 2);
		$this->assertFalse($this->checker->matches($v, '> 5.1.2'));
	}

	public function testGreaterThanDoesNotMatchLowerVersion(): void
	{
		$v = new Version(5, 1, 1);
		$this->assertFalse($this->checker->matches($v, '> 5.1.2'));
	}

	// ── < operator ──────────────────────────────────────────────────────────

	public function testLessThanMatchesLowerVersion(): void
	{
		$v = new Version(5, 1, 1);
		$this->assertTrue($this->checker->matches($v, '< 5.1.2'));
	}

	public function testLessThanDoesNotMatchEqualVersion(): void
	{
		$v = new Version(5, 1, 2);
		$this->assertFalse($this->checker->matches($v, '< 5.1.2'));
	}

	public function testLessThanDoesNotMatchHigherVersion(): void
	{
		$v = new Version(5, 2, 0);
		$this->assertFalse($this->checker->matches($v, '< 5.1.2'));
	}

	// ── = operator ──────────────────────────────────────────────────────────

	public function testEqualMatchesExactVersion(): void
	{
		$v = new Version(5, 1, 2);
		$this->assertTrue($this->checker->matches($v, '= 5.1.2'));
	}

	public function testEqualDoesNotMatchDifferentVersion(): void
	{
		$v = new Version(5, 1, 1);
		$this->assertFalse($this->checker->matches($v, '= 5.1.2'));
	}

	// ── != operator ─────────────────────────────────────────────────────────

	public function testNotEqualMatchesDifferentVersion(): void
	{
		$v = new Version(5, 1, 1);
		$this->assertTrue($this->checker->matches($v, '!= 5.1.2'));
	}

	public function testNotEqualDoesNotMatchSameVersion(): void
	{
		$v = new Version(5, 1, 2);
		$this->assertFalse($this->checker->matches($v, '!= 5.1.2'));
	}

	// ── comma-separated multi-constraint (S-032-13) ──────────────────────────

	public function testMultiConstraintMatchesVersionInRange(): void
	{
		// 5.0.5 is >= 5.0.0 and < 5.1.2 — should match
		$v = new Version(5, 0, 5);
		$this->assertTrue($this->checker->matches($v, '>= 5.0.0, < 5.1.2'));
	}

	public function testMultiConstraintDoesNotMatchVersionAtUpperBound(): void
	{
		// 5.1.2 is >= 5.0.0 but NOT < 5.1.2 — should NOT match
		$v = new Version(5, 1, 2);
		$this->assertFalse($this->checker->matches($v, '>= 5.0.0, < 5.1.2'));
	}

	public function testMultiConstraintDoesNotMatchVersionBelowLowerBound(): void
	{
		// 3.9.9 is < 5.0.0 — should NOT match
		$v = new Version(3, 9, 9);
		$this->assertFalse($this->checker->matches($v, '>= 5.0.0, < 5.1.2'));
	}

	// ── malformed token (S-032-09) ───────────────────────────────────────────

	public function testTokenWithoutOperatorIsTreatedAsGreaterThanOrEqual(): void
	{
		// A token without an operator (e.g., "5.1.2") is treated as ">= 5.1.2".
		$v = new Version(5, 2, 0);
		$this->assertTrue($this->checker->matches($v, '5.1.2'));
	}

	public function testTokenWithoutOperatorMatchesEqualVersion(): void
	{
		$v = new Version(5, 1, 2);
		$this->assertTrue($this->checker->matches($v, '5.1.2'));
	}

	public function testTokenWithoutOperatorDoesNotMatchLowerVersion(): void
	{
		$v = new Version(5, 1, 1);
		$this->assertFalse($this->checker->matches($v, '5.1.2'));
	}

	public function testMultiConstraintWithImplicitOperator(): void
	{
		// "5.0.0" is treated as ">= 5.0.0"
		// So "5.0.5" should match ">= 5.0.0, < 5.1.2"
		$v = new Version(5, 0, 5);
		$this->assertTrue($this->checker->matches($v, '5.0.0, < 5.1.2'));
	}

	public function testTrulyMalformedTokenIsSkippedAndRemainingConstraintsEvaluated(): void
	{
		// A token that can't be parsed as a version at all is skipped (returns true),
		// so only the valid ">= 5.0.0" constraint matters.
		Log::shouldReceive('warning')->once();
		$v = new Version(5, 0, 5);
		$this->assertTrue($this->checker->matches($v, 'not-a-version, >= 5.0.0'));
	}

	public function testTrulyMalformedTokenDoesNotBlockValidConstraintFromFailingMatch(): void
	{
		// Even with a skipped malformed token, a valid failing constraint returns false.
		Log::shouldReceive('warning')->once();
		$v = new Version(3, 0, 0);
		$this->assertFalse($this->checker->matches($v, 'not-a-version, >= 5.0.0'));
	}
}
