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

use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Models\SizeVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 019 (Friendly URLs) × the v3 API surface.
 *
 * {@see \App\Http\Middleware\ResolveAlbumSlug} originally covered only two
 * shapes: `album_id`/`parent_id`/`from_id` read through
 * {@see \Illuminate\Http\Request::input()} (query string and body — never
 * route parameters), and the `albumId` route parameter spelled in camelCase,
 * which only {@link routes/web_v2.php} uses.
 *
 * Every v3 album route instead carries the identifier in a snake_case route
 * segment `{album_id}` and reads it back via `$this->route('album_id')` in
 * the FormRequest's `prepareForValidation()`, so neither branch fired and
 * slugs were never translated. The failure was not even a clean 404: the v3
 * requests validate with {@see \App\Rules\RandomIDRule}, so a slug was
 * rejected as a malformed ID with a 422.
 *
 * These tests pin slug resolution across the whole `{album_id}` family.
 */
class AlbumSlugV3Test extends BaseApiWithDataTest
{
	private const SLUG = 'my-vacation-photos';

	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);

		// Slugs are a supporter-gated *edit* (StringRequireSupportRule on the
		// update requests), but resolution itself is not gated — assign the
		// slug at the DB level so this suite stays independent of SE status.
		DB::table('base_albums')
			->where('id', '=', $this->album1->id)
			->update(['slug' => self::SLUG]);
	}

	// ── /Albums/{album_id} family ─────────────────────────────────

	public function testChildrenRouteResolvesSlug(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/' . self::SLUG);

		$this->assertOk($response);
		self::assertSame([$this->subAlbum1->id], $response->json('ids'));
	}

	public function testChildrenBucketsRouteResolvesSlug(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/' . self::SLUG . '/buckets');

		$this->assertOk($response);
	}

	public function testChildrenRightsRouteResolvesSlug(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/' . self::SLUG . '/rights');

		$this->assertOk($response);
	}

	// ── /Albums/{album_id}/Photos family ──────────────────────────

	public function testPhotosRouteResolvesSlug(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/' . self::SLUG . '/Photos');

		$this->assertOk($response);
		self::assertEqualsCanonicalizing(
			[$this->photo1->id, $this->photo1b->id],
			$response->json('ids'),
		);
	}

	public function testPhotosBucketsRouteResolvesSlug(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/' . self::SLUG . '/Photos/buckets');

		$this->assertOk($response);
	}

	public function testPhotosDetailsRouteResolvesSlug(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3(
			'Albums/' . self::SLUG . '/Photos/details',
			['photo_ids' => [$this->photo1->id]],
		);

		$this->assertOk($response);
	}

	// ── /Asset/{album_id}/{photo_id}/{size_variant} ───────────────

	public function testAssetRouteResolvesSlug(): void
	{
		Storage::fake(StorageDiskType::LOCAL->value);
		$variant = SizeVariant::query()
			->where('photo_id', '=', $this->photo1->id)
			->where('type', '=', SizeVariantType::THUMB)
			->firstOrFail();
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, 'thumb-bytes');

		$response = $this->actingAs($this->userMayUpload1)
			->getV3('Asset/' . self::SLUG . '/' . $this->photo1->id . '/thumb');

		$response->assertOk();
		self::assertSame('thumb-bytes', $response->streamedContent());
	}

	// ── Passthrough / negative paths ──────────────────────────────

	/**
	 * A real 24-char ID must keep working on the very same routes.
	 */
	public function testRealIdStillResolvesOnSluggedAlbum(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/' . $this->album1->id);

		$this->assertOk($response);
		self::assertSame([$this->subAlbum1->id], $response->json('ids'));
	}

	/**
	 * An unknown slug passes through untouched and is rejected downstream,
	 * exactly as an unknown ID would be — resolution must not invent an album.
	 */
	public function testUnknownSlugIsRejected(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Albums/no-such-album');

		self::assertContains($response->getStatusCode(), [404, 422]);
	}

	/**
	 * Slug resolution must not bypass authorization (FR-019-11): a guest
	 * asking for a private album by slug is refused just as they would be by
	 * ID.
	 */
	public function testSlugResolutionRespectsAuthorization(): void
	{
		$response = $this->getJsonV3('Albums/' . self::SLUG);

		self::assertContains($response->getStatusCode(), [401, 403]);
	}
}
