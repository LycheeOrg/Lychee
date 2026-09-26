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

namespace Tests\Feature_v2\Album;

use App\Models\Configs;
use App\Models\Person;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 071 (FR-071-02): the per-album date scrubber override round-trips
 * through the album, tag album and person album update endpoints.
 */
class UpdateAlbumDateScrubberTest extends BaseApiWithDataTest
{
	/**
	 * @return array<string,array{0:?bool}>
	 */
	public static function overrideProvider(): array
	{
		return [
			'follow global' => [null],
			'forced on' => [true],
			'forced off' => [false],
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	private function albumPayload(mixed $value): array
	{
		return [
			'album_id' => $this->album1->id,
			'title' => 'title',
			'license' => 'none',
			'description' => '',
			'tags' => [],
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'album_sorting_column' => 'title',
			'album_sorting_order' => 'DESC',
			'album_aspect_ratio' => '1/1',
			'photo_layout' => null,
			'copyright' => '',
			'is_compact' => false,
			'is_pinned' => false,
			'header_id' => null,
			'cover_id' => null,
			'album_timeline' => null,
			'photo_timeline' => null,
			'is_date_scrubber_enabled' => $value,
		];
	}

	#[DataProvider('overrideProvider')]
	public function testAlbumRoundTrip(?bool $value): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $this->albumPayload($value));
		$this->assertOk($response);

		self::assertSame($value, $response->json('is_date_scrubber_enabled'));
		self::assertSame($value, $this->album1->fresh()->is_date_scrubber_enabled);
	}

	#[DataProvider('overrideProvider')]
	public function testTagAlbumRoundTrip(?bool $value): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('TagAlbum', [
			'album_id' => $this->tagAlbum1->id,
			'title' => 'title',
			'tags' => ['tag1'],
			'description' => '',
			'photo_sorting_column' => 'title',
			'photo_sorting_order' => 'ASC',
			'copyright' => '',
			'is_pinned' => false,
			'is_and' => true,
			'photo_layout' => null,
			'photo_timeline' => null,
			'is_date_scrubber_enabled' => $value,
		]);
		$this->assertOk($response);

		self::assertSame($value, $response->json('is_date_scrubber_enabled'));
	}

	#[DataProvider('overrideProvider')]
	public function testPersonAlbumRoundTrip(?bool $value): void
	{
		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		$person = Person::factory()->create(['name' => 'Alice', 'is_searchable' => true]);
		$response = $this->actingAs($this->userMayUpload1)->postJson('PersonAlbum', [
			'title' => 'person_album',
			'persons' => [$person->id],
			'is_and' => false,
		]);
		$this->assertOk($response);
		$album_id = $response->getOriginalContent();

		$response = $this->actingAs($this->userMayUpload1)->patchJson('PersonAlbum', [
			'album_id' => $album_id,
			'title' => 'person_album',
			'description' => '',
			'persons' => [$person->id],
			'is_and' => true,
			'photo_sorting_column' => null,
			'photo_sorting_order' => null,
			'copyright' => null,
			'photo_layout' => null,
			'photo_timeline' => null,
			'is_pinned' => false,
			'is_date_scrubber_enabled' => $value,
		]);
		$this->assertOk($response);

		self::assertSame($value, $response->json('is_date_scrubber_enabled'));
	}

	public function testNonBooleanIsRejected(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $this->albumPayload('yes'));
		$this->assertUnprocessable($response);
	}

	/**
	 * Omitting the field (e.g. the v7 frontend, which predates it) leaves the
	 * stored override unchanged, like `published_at` (Feature 068).
	 */
	public function testOmittedFieldLeavesOverrideUnchanged(): void
	{
		DB::table('base_albums')->where('id', '=', $this->album1->id)->update(['is_date_scrubber_enabled' => true]);
		$payload = $this->albumPayload(null);
		unset($payload['is_date_scrubber_enabled']);

		$response = $this->actingAs($this->userMayUpload1)->patchJson('Album', $payload);
		$this->assertOk($response);

		self::assertTrue($this->album1->fresh()->is_date_scrubber_enabled);
	}
}
