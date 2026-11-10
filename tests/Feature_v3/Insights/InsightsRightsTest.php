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

namespace Tests\Feature_v3\Insights;

use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers who may read which Insights scope and the period validation of
 * `GET /api/v3/Insights` (Feature 085, FR-085-04, FR-085-05,
 * S-085-02, S-085-03, S-085-07, S-085-09, S-085-10).
 */
class InsightsRightsTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	public function testGuestIsUnauthenticated(): void
	{
		$this->assertUnauthorized($this->getJsonV3('Insights', ['period' => 'library']));
	}

	public function testWithoutSupporterEditionPaymentIsRequired(): void
	{
		$this->resetSe();

		$this->getJsonV3WithUser('Insights', ['period' => 'library'])->assertStatus(402);
	}

	public function testUserReadsOwnLibrary(): void
	{
		$this->assertOk($this->getJsonV3WithUser('Insights', ['period' => 'library']));
	}

	public function testUserMayNameThemselves(): void
	{
		$this->assertOk($this->getJsonV3WithUser('Insights', ['period' => 'library', 'owner_id' => $this->userMayUpload1->id]));
	}

	public function testUserCannotReadAnotherOwner(): void
	{
		$this->assertForbidden($this->getJsonV3WithUser('Insights', ['period' => 'library', 'owner_id' => $this->userMayUpload2->id]));
	}

	public function testUserCannotReadTheWholeInstance(): void
	{
		$this->assertForbidden($this->getJsonV3WithUser('Insights', ['period' => 'library', 'whole_instance' => 1]));
	}

	public function testAdminReadsAnotherOwner(): void
	{
		$this->assertOk($this->actingAs($this->admin)->getJsonV3('Insights', ['period' => 'library', 'owner_id' => $this->userMayUpload2->id]));
	}

	public function testAdminReadsTheWholeInstance(): void
	{
		$this->assertOk($this->actingAs($this->admin)->getJsonV3('Insights', ['period' => 'library', 'whole_instance' => 1]));
	}

	public function testAdminNamingAnUnknownUserIsInvalid(): void
	{
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonV3('Insights', ['period' => 'library', 'owner_id' => 999999]));
	}

	public function testMissingPeriodIsInvalid(): void
	{
		$this->assertUnprocessable($this->getJsonV3WithUser('Insights'));
	}

	public function testYearPeriodNeedsAYear(): void
	{
		$this->assertUnprocessable($this->getJsonV3WithUser('Insights', ['period' => 'year']));
		$this->assertOk($this->getJsonV3WithUser('Insights', ['period' => 'year', 'year' => 2025]));
	}

	public function testRangeEndingBeforeItStartsIsInvalid(): void
	{
		$this->assertUnprocessable($this->getJsonV3WithUser('Insights', ['period' => 'range', 'from' => '2025-02-01', 'to' => '2025-01-31']));
		$this->assertOk($this->getJsonV3WithUser('Insights', ['period' => 'range', 'from' => '2025-01-31', 'to' => '2025-01-31']));
	}

	public function testRangeNeedsIsoDates(): void
	{
		$this->assertUnprocessable($this->getJsonV3WithUser('Insights', ['period' => 'range', 'from' => '31/01/2025', 'to' => '2025-02-01']));
	}

	/**
	 * @param array<string,mixed> $data
	 */
	private function getJsonV3WithUser(string $uri, array $data = []): \Illuminate\Testing\TestResponse
	{
		return $this->actingAs($this->userMayUpload1)->getJsonV3($uri, $data);
	}
}
