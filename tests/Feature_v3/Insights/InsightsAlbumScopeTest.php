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

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers Insights scoped to one album tree (Feature 085, FR-085-16,
 * S-085-20 … S-085-23).
 */
class InsightsAlbumScopeTest extends BaseApiWithDataTest
{
	use InsightsDataset;

	private Album $album_a1;
	private Photo $p7;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		$this->seedInsightsDataset();

		// A sub-album of A holding a photo uploaded by someone else, plus p1 a second time.
		$this->album_a1 = Album::factory()->children_of($this->album_a)->owned_by($this->photographer)->create();
		$this->p7 = Photo::factory()->owned_by($this->userMayUpload2)->in($this->album_a1)->create(['taken_at' => new Carbon('2025-05-05 12:00:00', 'UTC')]);
		$this->p1->albums()->attach($this->album_a1->id);
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	public function testOwnerReadsTheAlbumTree(): void
	{
		$response = $this->actingAs($this->photographer)->getJsonV3('Insights', ['period' => 'library', 'album_id' => $this->album_a->id]);

		$this->assertOk($response);
		// p1, p2 in A and p7 in A1 (uploaded by another user); p1 counted once.
		$response->assertJsonPath('overview.total', 3);
		$response->assertJsonPath('overview.albums', 2);
		$response->assertJsonPath('years', [2025]);
		$response->assertJsonPath('people.has_faces', true);
		$response->assertJsonPath('people.people', 1);
	}

	public function testSubAlbumAlone(): void
	{
		$response = $this->actingAs($this->photographer)->getJsonV3('Insights', ['period' => 'library', 'album_id' => $this->album_a1->id]);

		$this->assertOk($response);
		$response->assertJsonPath('overview.total', 2);
		$response->assertJsonPath('overview.albums', 1);
	}

	public function testPeriodInsideTheTree(): void
	{
		$response = $this->actingAs($this->photographer)->getJsonV3('Insights', ['period' => 'range', 'from' => '2025-05-01', 'to' => '2025-05-31', 'album_id' => $this->album_a->id]);

		$this->assertOk($response);
		$response->assertJsonPath('overview.total', 1);
		$response->assertJsonPath('overview.albums', 1);
	}

	public function testNonOwnerIsForbidden(): void
	{
		$this->assertForbidden($this->actingAs($this->userMayUpload2)->getJsonV3('Insights', ['period' => 'library', 'album_id' => $this->album_a->id]));
	}

	public function testAdminReadsAnyAlbum(): void
	{
		$response = $this->actingAs($this->admin)->getJsonV3('Insights', ['period' => 'library', 'album_id' => $this->album_a->id]);

		$this->assertOk($response);
		$response->assertJsonPath('overview.total', 3);
	}

	public function testUnknownAlbumIsInvalid(): void
	{
		$this->assertUnprocessable($this->actingAs($this->photographer)->getJsonV3('Insights', ['period' => 'library', 'album_id' => 'abcdefghijklmnopqrstuvwx']));
	}

	public function testTagAlbumIsInvalid(): void
	{
		$this->assertUnprocessable($this->actingAs($this->userMayUpload1)->getJsonV3('Insights', ['period' => 'library', 'album_id' => $this->tagAlbum1->id]));
	}

	public function testAlbumScopeIsCachedApartAndRefreshed(): void
	{
		$own = $this->actingAs($this->photographer)->getJsonV3('Insights', ['period' => 'library']);
		$own->assertJsonPath('overview.total', 6);

		Photo::factory()->owned_by($this->photographer)->in($this->album_a1)->create();
		$album = $this->actingAs($this->photographer)->getJsonV3('Insights', ['period' => 'library', 'album_id' => $this->album_a->id]);

		$album->assertJsonPath('overview.total', 4);
	}
}
