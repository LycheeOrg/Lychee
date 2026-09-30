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

namespace Tests\Feature_v3\Album;

use App\Models\Configs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers Feature 062 FR-062-17, S-062-18, S-062-30..33:
 * `GET /api/v3/Albums/root/config`.
 */
class AlbumRootConfigV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	/** S-062-18 */
	public function testFlagOffReturns403(): void
	{
		config(['features.struct-of-array' => false]);

		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Albums/root/config'));
	}

	/** S-062-30 */
	public function testGuestGetsSameConfigAndRightsAsV2(): void
	{
		Auth::logout();
		$v2 = $this->getJson('Albums');
		$this->assertOk($v2);

		$response = $this->getJsonV3('Albums/root/config');
		$this->assertOk($response);

		self::assertSame($v2->json('config'), $response->json('config'));
		self::assertSame($v2->json('rights'), $response->json('rights'));
	}

	/** S-062-31 */
	public function testGuestWithLoginRequiredIsUnauthorized(): void
	{
		Configs::set('login_required', '1');
		Auth::logout();

		$response = $this->getJsonV3('Albums/root/config');
		$this->assertUnauthorized($response);
		$response->assertJson(['message' => 'Login required.']);
	}

	/** S-062-32 */
	public function testUploaderGetsUploadRight(): void
	{
		$v2 = $this->actingAs($this->userMayUpload1)->getJson('Albums');
		$this->assertOk($v2);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/root/config');
		$this->assertOk($response);

		self::assertTrue($response->json('rights.can_upload'));
		self::assertSame($v2->json('config'), $response->json('config'));
		self::assertSame($v2->json('rights'), $response->json('rights'));
	}

	/** S-062-33 */
	public function testRunsNoAlbumOrPhotoQuery(): void
	{
		$this->actingAs($this->userMayUpload1);

		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->assertOk($this->getJsonV3('Albums/root/config'));
		$queries = DB::getQueryLog();
		DB::disableQueryLog();

		$album_or_photo_queries = array_filter(
			$queries,
			// Identifier quoting is driver-specific; strip both styles before matching.
			fn (array $q) => preg_match('/\b(albums|base_albums|photos)\b/', str_replace(['"', '`'], '', $q['query'])) === 1
		);
		self::assertSame([], array_values($album_or_photo_queries));
	}
}
