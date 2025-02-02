<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v2\Statistics;

use App\Models\Configs;
use LycheeVerify\Http\Middleware\VerifySupporterStatus;
use Tests\Feature_v2\Base\BaseApiV2Test;

class AlbumSpaceTest extends BaseApiV2Test
{
	public function testAlbumSpaceTestUnauthorized(): void
	{
		Configs::set('cache_enabled', '0');
		Configs::invalidateCache();

		$response = $this->getJson('Statistics::albumSpace');
		$this->assertSupporterRequired($response);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->getJson('Statistics::albumSpace');
		$this->assertUnauthorized($response);
	}

	public function testAlbumSpaceTestAuthorized(): void
	{
		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->userMayUpload1)->getJson('Statistics::albumSpace');
		$this->assertOk($response);
		self::assertCount(2, $response->json());
		self::assertEquals($this->album1->title, $response->json()[0]['title']);
		self::assertEquals($this->subAlbum1->title, $response->json()[1]['title']);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->userMayUpload1)->getJson('Statistics::albumSpace?album_id=' . $this->album1->id);
		$this->assertOk($response);
		self::assertCount(2, $response->json());
		self::assertEquals($this->album1->title, $response->json()[0]['title']);
		self::assertEquals($this->subAlbum1->title, $response->json()[1]['title']);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->userMayUpload1)->getJson('Statistics::albumSpace?album_id=' . $this->subAlbum1->id);
		$this->assertOk($response);
		self::assertCount(1, $response->json());
		self::assertEquals($this->subAlbum1->title, $response->json()[0]['title']);

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)->actingAs($this->admin)->getJson('Statistics::albumSpace');
		$this->assertOk($response);
		self::assertCount(7, $response->json());
	}
}