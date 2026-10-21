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

namespace Tests\Feature_v3\Photo;

use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Mockery\MockInterface;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers `GET /Albums/{album_id}/Photos/urls` (Feature 102, FR-102-05,
 * S-102-08 … S-102-12, ADR-102-01).
 */
class PhotoUrlsV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);
	}

	public function tearDown(): void
	{
		Configs::set('temporary_image_link_enabled', '0');
		Configs::set('temporary_image_link_when_logged_in', '0');
		Configs::set('secure_image_link_enabled', '0');
		Configs::set('watermark_enabled', '0');
		Configs::set('watermark_logged_in_users_enabled', '0');
		parent::tearDown();
	}

	private function variantOf(Photo $photo, SizeVariantType $type): SizeVariant
	{
		return SizeVariant::query()
			->where('photo_id', '=', $photo->id)
			->where('type', '=', $type)
			->firstOrFail();
	}

	private function deleteVariants(Photo $photo, SizeVariantType ...$types): void
	{
		SizeVariant::query()->where('photo_id', '=', $photo->id)->whereIn('type', $types)->delete();
	}

	private function localUrl(string $short_path): string
	{
		return Storage::disk(StorageDiskType::LOCAL->value)->url($short_path);
	}

	private function urlsOf(string $album_id, array $photo_ids, ?string $density = null): \Illuminate\Testing\TestResponse
	{
		$params = ['photo_ids' => $photo_ids];
		if ($density !== null) {
			$params['density'] = $density;
		}

		return $this->getJsonV3("Albums/{$album_id}/Photos/urls", $params);
	}

	// ── Direct URLs (S-102-08) ─────────────────────────────────────

	public function testOwnerGetsDirectUrls(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id]);

		$this->assertOk($response);
		$response->assertExactJson([
			'ids' => [$this->photo1->id],
			'grid_urls' => [$this->localUrl($this->variantOf($this->photo1, SizeVariantType::SMALL2X)->short_path)],
			'thumb_urls' => [$this->localUrl($this->variantOf($this->photo1, SizeVariantType::THUMB)->short_path)],
		]);
	}

	public function testGridUrlFallsBackToSmallThenThumb(): void
	{
		$this->deleteVariants($this->photo1, SizeVariantType::SMALL2X);
		$small = $this->localUrl($this->variantOf($this->photo1, SizeVariantType::SMALL)->short_path);
		$thumb = $this->localUrl($this->variantOf($this->photo1, SizeVariantType::THUMB)->short_path);

		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id]);
		$this->assertOk($response);
		self::assertSame([$small], $response->json('grid_urls'));
		self::assertSame([$thumb], $response->json('thumb_urls'));

		$this->deleteVariants($this->photo1, SizeVariantType::SMALL);
		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id]);
		$this->assertOk($response);
		self::assertSame([$thumb], $response->json('grid_urls'));
	}

	public function testDensityTwoKeepsSmall2xFirst(): void
	{
		$small2x = $this->localUrl($this->variantOf($this->photo1, SizeVariantType::SMALL2X)->short_path);

		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id], '2');

		$this->assertOk($response);
		self::assertSame([$small2x], $response->json('grid_urls'));
	}

	public function testDensityOneGivesSmallThenSmall2xThenThumb(): void
	{
		$small = $this->localUrl($this->variantOf($this->photo1, SizeVariantType::SMALL)->short_path);
		$small2x = $this->localUrl($this->variantOf($this->photo1, SizeVariantType::SMALL2X)->short_path);
		$thumb = $this->localUrl($this->variantOf($this->photo1, SizeVariantType::THUMB)->short_path);

		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id], '1');
		$this->assertOk($response);
		self::assertSame([$small], $response->json('grid_urls'));
		self::assertSame([$thumb], $response->json('thumb_urls'));

		$this->deleteVariants($this->photo1, SizeVariantType::SMALL);
		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id], '1');
		$this->assertOk($response);
		self::assertSame([$small2x], $response->json('grid_urls'));

		$this->deleteVariants($this->photo1, SizeVariantType::SMALL2X);
		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id], '1');
		$this->assertOk($response);
		self::assertSame([$thumb], $response->json('grid_urls'));
	}

	public function testUnknownDensityIsUnprocessable(): void
	{
		$this->assertUnprocessable($this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id], '3'));
	}

	public function testNullWithoutRows(): void
	{
		$this->deleteVariants($this->photo1, SizeVariantType::SMALL2X, SizeVariantType::SMALL, SizeVariantType::THUMB);

		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id]);

		$this->assertOk($response);
		$response->assertExactJson(['ids' => [$this->photo1->id], 'grid_urls' => [null], 'thumb_urls' => [null]]);
	}

	// ── Protected viewer (S-102-09) ────────────────────────────────

	public function testTemporaryLinkRequiredGivesNull(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		Configs::set('temporary_image_link_when_logged_in', '1');

		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id]);

		$this->assertOk($response);
		$response->assertExactJson(['ids' => [$this->photo1->id], 'grid_urls' => [null], 'thumb_urls' => [null]]);
	}

	public function testTemporaryLinkNotRequiredForLoggedInGivesUrls(): void
	{
		Configs::set('temporary_image_link_enabled', '1');

		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id]);

		$this->assertOk($response);
		self::assertNotNull($response->json('grid_urls.0'));
	}

	public function testSecureLinksGiveNull(): void
	{
		Configs::set('secure_image_link_enabled', '1');

		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id]);

		$this->assertOk($response);
		$response->assertExactJson(['ids' => [$this->photo1->id], 'grid_urls' => [null], 'thumb_urls' => [null]]);
	}

	// ── Watermark and S3 (S-102-10) ────────────────────────────────

	public function testWatermarkedPathWhenWatermarkApplies(): void
	{
		Configs::set('watermark_enabled', '1');
		Configs::set('watermark_logged_in_users_enabled', '1');
		$variant = $this->variantOf($this->photo1, SizeVariantType::SMALL2X);
		$variant->short_path_watermarked = 'watermarked/' . $variant->short_path;
		$variant->save();

		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id]);

		$this->assertOk($response);
		self::assertSame([$this->localUrl($variant->short_path_watermarked)], $response->json('grid_urls'));
	}

	public function testS3VariantGetsS3UrlEvenWhenLocalFilesAreProtected(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		Configs::set('temporary_image_link_when_logged_in', '1');
		$variant = $this->variantOf($this->photo1, SizeVariantType::SMALL2X);
		$variant->storage_disk = StorageDiskType::S3;
		$variant->save();
		// Private bucket: S3's own presigned URL.
		config(['filesystems.disks.s3.visibility' => 'private']);

		$aws_adapter = \Mockery::mock(AwsS3V3Adapter::class);
		$s3_disk = \Mockery::mock(FilesystemAdapter::class, function (MockInterface $mock) use ($aws_adapter, $variant): void {
			$mock->shouldReceive('getAdapter')->andReturn($aws_adapter);
			$mock->shouldReceive('temporaryUrl')
				->with($variant->short_path, \Mockery::any())
				->andReturn('https://example-bucket.s3.amazonaws.com/signed-url');
		});
		$real_manager = Storage::getFacadeRoot();
		Storage::partialMock();
		Storage::shouldReceive('disk')->andReturnUsing(
			fn (?string $name = null) => $name === StorageDiskType::S3->value ? $s3_disk : $real_manager->disk($name)
		);

		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo1->id]);

		$this->assertOk($response);
		self::assertSame(['https://example-bucket.s3.amazonaws.com/signed-url'], $response->json('grid_urls'));
		self::assertSame([null], $response->json('thumb_urls'));
	}

	// ── Scoping and validation (S-102-11) ──────────────────────────

	public function testIdsOutsideTheAlbumAreLeftOut(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, [$this->photo2->id, $this->photo1->id, 'AAAAAAAAAAAAAAAAAAAAAAAA']);

		$this->assertOk($response);
		self::assertSame([$this->photo1->id], $response->json('ids'));
		self::assertCount(1, $response->json('grid_urls'));
		self::assertCount(1, $response->json('thumb_urls'));
	}

	public function testNoIdsReturns422(): void
	{
		$this->assertUnprocessable($this->actingAs($this->userMayUpload1)->getJsonV3("Albums/{$this->album1->id}/Photos/urls"));
		$this->assertUnprocessable($this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, []));
	}

	public function test301IdsReturns422(): void
	{
		$ids = array_fill(0, 301, $this->photo1->id);

		$this->assertUnprocessable($this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, $ids));
	}

	public function testInvalidIdReturns422(): void
	{
		$this->assertUnprocessable($this->actingAs($this->userMayUpload1)->urlsOf($this->album1->id, ['not-an-id']));
	}

	public function testFlagOffReturns403(): void
	{
		config(['features.struct-of-array' => false]);

		$this->assertForbidden($this->actingAs($this->admin)->urlsOf($this->album1->id, [$this->photo1->id]));
	}

	public function testGuestOnPrivateAlbumReturns401(): void
	{
		$this->assertUnauthorized($this->urlsOf($this->album1->id, [$this->photo1->id]));
	}

	public function testOtherUserReturns403(): void
	{
		$this->assertForbidden($this->actingAs($this->userNoUpload)->urlsOf($this->album1->id, [$this->photo1->id]));
	}

	public function testUnknownAlbumReturns404(): void
	{
		$this->assertNotFound($this->actingAs($this->userMayUpload1)->urlsOf('AAAAAAAAAAAAAAAAAAAAAAAA', [$this->photo1->id]));
	}

	// ── Smart albums and timeline (S-102-12) ───────────────────────

	public function testSmartAlbumIdSucceeds(): void
	{
		$this->photo1->is_highlighted = true;
		$this->photo1->save();

		$response = $this->actingAs($this->userMayUpload1)->urlsOf('highlighted', [$this->photo1->id]);

		$this->assertOk($response);
		self::assertSame([$this->photo1->id], $response->json('ids'));
		self::assertNotNull($response->json('grid_urls.0'));
	}

	public function testTimelineIdSucceeds(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->urlsOf('timeline', [$this->photo1->id]);

		$this->assertOk($response);
		self::assertSame([$this->photo1->id], $response->json('ids'));
	}

	public function testTagAlbumIdSucceeds(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->urlsOf($this->tagAlbum1->id, [$this->photo1->id, $this->photo1b->id]);

		$this->assertOk($response);
		self::assertSame([$this->photo1->id], $response->json('ids'));
	}

	public function testIdsKeepRequestOrder(): void
	{
		$album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$first = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();
		$second = Photo::factory()->owned_by($this->userMayUpload1)->in($album)->create();

		$response = $this->actingAs($this->userMayUpload1)->urlsOf($album->id, [$second->id, $first->id]);

		$this->assertOk($response);
		self::assertSame([$second->id, $first->id], $response->json('ids'));
	}
}
