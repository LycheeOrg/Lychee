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
use App\Enum\SmartAlbumType;
use App\Enum\StorageDiskType;
use App\Models\AlbumUserThumb;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Repositories\ConfigManager;
use App\Services\TemporaryLinkSigner;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Mockery\MockInterface;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Covers Feature 056 Branch & Scenario Matrix S-056-01..17.
 *
 * The route is `Asset/{album_id}/{photo_id}/{size_variant}`: every request
 * carries the album the caller claims to be viewing the photo through
 * (see {@link \App\Http\Requests\Photo\GetPhotoAssetRequest}). `album_id` is
 * always a real album's ID here (`album1`/`album4`) since photo1/photo4 are
 * directly cataloged in them.
 *
 * `size_variant` is restricted to thumbnail-class tokens
 * (small2x/small/thumb2x/thumb/placeholder,
 * see {@link \App\Enum\SizeVariantAssetType}) — medium/original/raw are not
 * served through this endpoint, so there is no thumb-vs-full-photo access
 * split to test here (unlike a plain {@see \App\Policies\PhotoPolicy}
 * check): album-level access via
 * {@see \App\Policies\AlbumPolicy::CAN_ACCESS} is the only gate.
 */
class PhotoAssetV3Test extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		Storage::fake(StorageDiskType::LOCAL->value);
	}

	private function thumbVariantOf(Photo $photo): SizeVariant
	{
		return SizeVariant::query()
			->where('photo_id', '=', $photo->id)
			->where('type', '=', SizeVariantType::THUMB)
			->firstOrFail();
	}

	private function smallVariantOf(Photo $photo): SizeVariant
	{
		return SizeVariant::query()
			->where('photo_id', '=', $photo->id)
			->where('type', '=', SizeVariantType::SMALL)
			->firstOrFail();
	}

	private function small2xVariantOf(Photo $photo): SizeVariant
	{
		return SizeVariant::query()
			->where('photo_id', '=', $photo->id)
			->where('type', '=', SizeVariantType::SMALL2X)
			->firstOrFail();
	}

	private function thumb2xVariantOf(Photo $photo): SizeVariant
	{
		return SizeVariant::query()
			->where('photo_id', '=', $photo->id)
			->where('type', '=', SizeVariantType::THUMB2X)
			->firstOrFail();
	}

	private function putBytes(SizeVariant $variant, string $bytes = 'thumb-bytes'): void
	{
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, $bytes);
	}

	/**
	 * @return array<string,string>
	 */
	private function signedHeaders(): array
	{
		return ['X-Mac' => (new TemporaryLinkSigner())->sign()];
	}

	/**
	 * S-056-01: Authenticated owner requests own photo's THUMB variant, no
	 * signature headers, signatureRequired() false → 200, correct bytes
	 * streamed.
	 */
	public function testAuthenticatedOwnerRetrievesThumb(): void
	{
		$variant = $this->thumbVariantOf($this->photo1);
		$this->putBytes($variant);

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/thumb");

		$response->assertOk();
		self::assertSame('thumb-bytes', $response->streamedContent());
	}

	/**
	 * S-056-02: Guest requests a public album's photo THUMB variant with a
	 * valid, unexpired signature, temporary_image_link_enabled=true → 200.
	 */
	public function testGuestWithValidSignatureOnPublicAlbumSucceeds(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$response = $this->getV3("Asset/{$this->album4->id}/{$this->photo4->id}/thumb", $this->signedHeaders());

		$response->assertOk();
	}

	/**
	 * S-056-03: Guest requests the same as S-056-02 but the album is not
	 * public → 403, despite a validly-signed link.
	 */
	public function testGuestWithValidSignatureButPrivateAlbumIsForbidden(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$variant = $this->thumbVariantOf($this->photo1);
		$this->putBytes($variant);

		$response = $this->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/thumb", $this->signedHeaders());

		$response->assertForbidden();
	}

	/**
	 * S-056-04: Guest requests with no headers at all,
	 * temporary_image_link_enabled=true → 401.
	 */
	public function testGuestWithNoHeadersIsUnauthorized(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$response = $this->getV3("Asset/{$this->album4->id}/{$this->photo4->id}/thumb");

		$response->assertUnauthorized();
	}

	/**
	 * S-056-05: temporary_image_link_enabled=false → signatureRequired() is
	 * false for guests too (Q-056-05), so the request falls through to the
	 * ordinary AlbumPolicy check like any other unauthenticated request → 200
	 * on a public album, regardless of headers.
	 */
	public function testDisabledFeatureFallsBackToAlbumPolicyForGuest(): void
	{
		Configs::set('temporary_image_link_enabled', '0');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$response = $this->getV3("Asset/{$this->album4->id}/{$this->photo4->id}/thumb", $this->signedHeaders());

		$response->assertOk();
	}

	/**
	 * S-056-06: X-Mac that doesn't match the current/grace-window code →
	 * 401.
	 */
	public function testTamperedMacIsUnauthorized(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$headers = $this->signedHeaders();
		$headers['X-Mac'] = substr($headers['X-Mac'], 0, -1) . (str_ends_with($headers['X-Mac'], '1') ? '2' : '1');

		$response = $this->getV3("Asset/{$this->album4->id}/{$this->photo4->id}/thumb", $headers);

		$response->assertUnauthorized();
	}

	/**
	 * S-056-07: X-Mac minted several steps in the past (older than the
	 * signer's one-step grace window) → 401 (expired).
	 */
	public function testStaleMacIsUnauthorized(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$life = resolve(ConfigManager::class)->getValueAsInt('temporary_image_link_life_in_seconds');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$this->travelTo(now()->subSeconds($life * 3));
		$headers = $this->signedHeaders();
		$this->travelBack();

		$response = $this->getV3("Asset/{$this->album4->id}/{$this->photo4->id}/thumb", $headers);

		$response->assertUnauthorized();
	}

	/**
	 * S-056-08: X-Mac minted several steps in the future → 401.
	 */
	public function testFutureMacIsUnauthorized(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$life = resolve(ConfigManager::class)->getValueAsInt('temporary_image_link_life_in_seconds');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$this->travelTo(now()->addSeconds($life * 3));
		$headers = $this->signedHeaders();
		$this->travelBack();

		$response = $this->getV3("Asset/{$this->album4->id}/{$this->photo4->id}/thumb", $headers);

		$response->assertUnauthorized();
	}

	/**
	 * S-056-09: A garbage X-Mac value (never a valid code) → 401.
	 */
	public function testGarbageMacIsUnauthorized(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$response = $this->getV3("Asset/{$this->album4->id}/{$this->photo4->id}/thumb", ['X-Mac' => 'not-a-code']);

		$response->assertUnauthorized();
	}

	/**
	 * S-056-10: Authenticated non-admin user,
	 * temporary_image_link_when_logged_in=true and no headers supplied →
	 * 401, even though a session exists.
	 */
	public function testLoggedInUserStillRequiredToSignWithoutHeadersIsUnauthorized(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		Configs::set('temporary_image_link_when_logged_in', '1');
		$variant = $this->thumbVariantOf($this->photo1);
		$this->putBytes($variant);

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/thumb");

		$response->assertUnauthorized();
	}

	/**
	 * S-056-11: Authenticated admin, temporary_image_link_when_admin=false,
	 * no headers → 200 (admin session alone suffices per config).
	 */
	public function testAdminExemptedFromSigningReturnsOk(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		Configs::set('temporary_image_link_when_admin', '0');
		$variant = $this->thumbVariantOf($this->photo1);
		$this->putBytes($variant);

		$response = $this->actingAs($this->admin)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/thumb");

		$response->assertOk();
	}

	/**
	 * S-056-12: Request for size_variant=PLACEHOLDER on a photo with no
	 * stored PLACEHOLDER variant → 404 (photo1's factory-created variants
	 * never include PLACEHOLDER).
	 */
	public function testMissingSizeVariantRowReturnsNotFound(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/placeholder");

		$response->assertNotFound();
	}

	/**
	 * S-056-13: Unrecognized size_variant token → 422.
	 */
	public function testUnrecognizedSizeVariantTokenReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/huge");

		$response->assertUnprocessable();
	}

	/**
	 * S-056-14: Unknown photo_id → 404.
	 */
	public function testUnknownPhotoIdReturnsNotFound(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/000000000000000000000000/thumb");

		$response->assertNotFound();
	}

	/**
	 * S-056-15: Authorized request for a size_variant stored on the S3 disk
	 * → 302 redirect to a native S3 temporary URL, no bytes proxied through
	 * Lychee.
	 */
	public function testS3BackedVariantRedirectsToTemporaryUrl(): void
	{
		$variant = $this->smallVariantOf($this->photo1);
		$variant->storage_disk = StorageDiskType::S3;
		$variant->save();

		$aws_adapter = \Mockery::mock(AwsS3V3Adapter::class);
		$s3_disk = \Mockery::mock(FilesystemAdapter::class, function (MockInterface $mock) use ($aws_adapter, $variant): void {
			$mock->shouldReceive('getAdapter')->andReturn($aws_adapter);
			$mock->shouldReceive('temporaryUrl')
				->once()
				->with($variant->short_path, \Mockery::any())
				->andReturn('https://example-bucket.s3.amazonaws.com/signed-url');
			// Resolving album1 also eagerly loads its cover/thumb (Album::$with),
			// which may compute a URL for whichever photo/variant was picked as
			// the album's thumbnail — incidental to what this test asserts, but
			// still needs a stub since it can land on the S3 disk too.
			$mock->shouldReceive('url')->andReturn('https://example-bucket.s3.amazonaws.com/incidental-thumb-url');
		});

		// Only the 's3' disk is faked; every other disk name (e.g. the
		// 'images' local disk, already faked in setUp()) must still resolve
		// through the real manager, so we capture it before mocking the
		// facade and delegate non-S3 calls to it directly.
		$real_manager = Storage::getFacadeRoot();
		Storage::partialMock();
		Storage::shouldReceive('disk')->andReturnUsing(
			fn (?string $name = null) => $name === StorageDiskType::S3->value ? $s3_disk : $real_manager->disk($name)
		);

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/small");

		$response->assertRedirect('https://example-bucket.s3.amazonaws.com/signed-url');
	}

	/**
	 * S-056-16: Authorized request for size_variant=SMALL where the
	 * requesting viewer meets watermark conditions → served file is the
	 * watermarked path, not the plain stored path.
	 */
	public function testWatermarkedPathServedWhenConditionsMet(): void
	{
		Configs::set('watermark_enabled', '1');
		Configs::set('watermark_logged_in_users_enabled', '1');

		$variant = $this->smallVariantOf($this->photo1);
		$variant->short_path_watermarked = 'watermarked/' . $variant->short_path;
		$variant->save();

		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, 'plain-bytes');
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path_watermarked, 'watermarked-bytes');

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/small");

		$response->assertOk();
		self::assertSame('watermarked-bytes', $response->streamedContent());
	}

	/**
	 * NFR-056-04: Full signatureRequired() config/caller-state matrix.
	 *
	 * Enumerates all 2×2×2 combinations of temporary_image_link_enabled /
	 * _when_logged_in / _when_admin, crossed with (guest / logged-in
	 * non-admin / admin) caller state, all requesting without any X-Mac
	 * header. Truth table (several combinations collapse
	 * to the same outcome):
	 *
	 * - Guest: 401 iff enabled (guests are only ever authorized via a valid
	 *   temporary link when the feature is on, and no headers means that
	 *   link can never be valid); when disabled, signatureRequired() is
	 *   false for guests too, so the request falls through to AlbumPolicy
	 *   and succeeds on a public album (FR-056-05, Q-056-05) — 200.
	 * - Logged-in non-admin: 401 iff (enabled && when_logged_in), else 200
	 *   (session alone suffices). `when_admin` is irrelevant to this caller.
	 * - Admin: 401 iff (enabled && when_admin), else 200 (session alone
	 *   suffices, admin bypasses AlbumPolicy too). `when_logged_in` is
	 *   irrelevant to this caller.
	 */
	public function testSignatureRequiredConfigCallerStateMatrix(): void
	{
		$variant1 = $this->thumbVariantOf($this->photo1);
		$this->putBytes($variant1);
		$variant4 = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant4);

		foreach ([false, true] as $enabled) {
			foreach ([false, true] as $when_logged_in) {
				foreach ([false, true] as $when_admin) {
					Configs::set('temporary_image_link_enabled', $enabled ? '1' : '0');
					Configs::set('temporary_image_link_when_logged_in', $when_logged_in ? '1' : '0');
					Configs::set('temporary_image_link_when_admin', $when_admin ? '1' : '0');
					$case = 'enabled=' . var_export($enabled, true) . ' when_logged_in=' . var_export($when_logged_in, true) . ' when_admin=' . var_export($when_admin, true);

					Auth::logout();
					$guest_response = $this->getV3("Asset/{$this->album4->id}/{$this->photo4->id}/thumb");
					$expected_guest = $enabled ? 401 : 200;
					self::assertSame($expected_guest, $guest_response->getStatusCode(), "guest, {$case}");

					$non_admin_response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/thumb");
					$expected_non_admin = ($enabled && $when_logged_in) ? 401 : 200;
					self::assertSame($expected_non_admin, $non_admin_response->getStatusCode(), "non-admin, {$case}");

					$admin_response = $this->actingAs($this->admin)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/thumb");
					$expected_admin = ($enabled && $when_admin) ? 401 : 200;
					self::assertSame($expected_admin, $admin_response->getStatusCode(), "admin, {$case}");
				}
			}
		}
	}

	/**
	 * album_id must actually contain photo_id: a real album/photo pair that
	 * exist independently, but where the photo isn't cataloged in the given
	 * album (nor is it that album's cover), is forbidden even though both
	 * IDs individually resolve and the caller can access the album itself.
	 */
	public function testPhotoNotInGivenAlbumIsForbidden(): void
	{
		$variant = $this->thumbVariantOf($this->photo2);
		$this->putBytes($variant);

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo2->id}/thumb");

		$response->assertForbidden();
	}

	/**
	 * 2026-09-02 amendment (Feature 063 FR-056-08): a smart album's cached
	 * `album_user_thumbs` cover, still matching its live `smart_photo_condition`
	 * (`photoUnsorted` genuinely has no album, i.e. is genuinely unsorted),
	 * resolves via the new `isComputedAlbumThumb()` branch — mirrors
	 * `TagAlbum`/`PersonAlbum`'s pre-existing (previously untested) behavior.
	 */
	public function testSmartAlbumCachedCoverStillMatchingLiveConditionSucceeds(): void
	{
		AlbumUserThumb::query()->create([
			'user_id' => $this->userMayUpload1->id,
			'album_id' => SmartAlbumType::UNSORTED->value,
			'photo_id' => $this->photoUnsorted->id,
		]);
		$variant = $this->thumbVariantOf($this->photoUnsorted);
		$this->putBytes($variant);

		$response = $this->actingAs($this->userMayUpload1)->getV3('Asset/' . SmartAlbumType::UNSORTED->value . "/{$this->photoUnsorted->id}/thumb");

		$response->assertOk();
	}

	/**
	 * 2026-09-02 amendment (Feature 063 FR-056-08, Q-063-15): the actual
	 * reason this branch exists — a photo cached as a smart album's cover
	 * (Feature 062 FR-062-16) that has since fallen out of that album's own
	 * live condition (here: `photo1` is not `is_highlighted`, so it would
	 * never match `HighlightedAlbum`'s `smart_photo_condition`) must still
	 * resolve through the Asset endpoint rather than 403 — the cache entry
	 * itself, not the live query, is what legitimizes the cover exception,
	 * exactly like the pre-existing `TagAlbum`/`PersonAlbum` branch.
	 */
	public function testSmartAlbumCachedCoverNoLongerMatchingLiveConditionStillSucceeds(): void
	{
		self::assertFalse($this->photo1->is_highlighted, 'Fixture assumption: photo1 must not be highlighted.');

		AlbumUserThumb::query()->create([
			'user_id' => $this->userMayUpload1->id,
			'album_id' => SmartAlbumType::HIGHLIGHTED->value,
			'photo_id' => $this->photo1->id,
		]);
		$variant = $this->thumbVariantOf($this->photo1);
		$this->putBytes($variant);

		$response = $this->actingAs($this->userMayUpload1)->getV3('Asset/' . SmartAlbumType::HIGHLIGHTED->value . "/{$this->photo1->id}/thumb");

		$response->assertOk();
	}

	/**
	 * A photo that is neither the smart album's cached cover nor a live
	 * match still falls through to the generic membership check → 403 —
	 * the new branch is a narrow cache exception, not a blanket bypass.
	 */
	public function testSmartAlbumUncachedNonMatchingPhotoIsForbidden(): void
	{
		self::assertFalse($this->photo1->is_highlighted, 'Fixture assumption: photo1 must not be highlighted.');
		$variant = $this->thumbVariantOf($this->photo1);
		$this->putBytes($variant);

		$response = $this->actingAs($this->userMayUpload1)->getV3('Asset/' . SmartAlbumType::HIGHLIGHTED->value . "/{$this->photo1->id}/thumb");

		$response->assertForbidden();
	}

	/**
	 * Unknown album_id → 404, same as an unknown photo_id.
	 */
	public function testUnknownAlbumIdReturnsNotFound(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getV3('Asset/000000000000000000000000/' . $this->photo1->id . '/thumb');

		$response->assertNotFound();
	}

	/**
	 * PhotoAssetController::fallback() single-step case: the requested
	 * variant's DB row exists but its file is missing from disk → falls
	 * back exactly one step (small2x → small) and serves that file instead.
	 */
	public function testFallbackServesNextSmallerSizeWhenFileMissing(): void
	{
		$this->putBytes($this->smallVariantOf($this->photo1), 'small-bytes');
		// small2x row exists (factory creates all 7 variants) but no bytes are put for it.

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/small2x");

		$response->assertOk();
		self::assertSame('small-bytes', $response->streamedContent());
	}

	/**
	 * PhotoAssetController::fallback() multi-step case: small2x and small
	 * both have DB rows but no files, so the chain must walk small2x → small
	 * → thumb before finding a file.
	 */
	public function testFallbackWalksMultipleStepsWhenIntermediateFilesMissing(): void
	{
		$this->putBytes($this->thumbVariantOf($this->photo1), 'thumb-bytes');
		// small2x and small rows exist but no bytes are put for either.

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/small2x");

		$response->assertOk();
		self::assertSame('thumb-bytes', $response->streamedContent());
	}

	/**
	 * thumb2x falls back to thumb directly (its only fallback target) when
	 * the thumb2x file is missing.
	 */
	public function testFallbackFromThumb2xToThumbWhenFileMissing(): void
	{
		$this->putBytes($this->thumbVariantOf($this->photo1), 'thumb-bytes');
		// thumb2x row exists but no bytes are put for it.

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/thumb2x");

		$response->assertOk();
		self::assertSame('thumb-bytes', $response->streamedContent());
	}

	/**
	 * PhotoAssetController::fallback() null-row case: the intermediate
	 * fallback target (small) has no DB row at all (not merely a missing
	 * file), so fallback() must recurse straight past it to thumb without
	 * dereferencing a null SizeVariant.
	 */
	public function testFallbackSkipsFallbackTypeWithNoDbRowAtAll(): void
	{
		$this->putBytes($this->thumbVariantOf($this->photo1), 'thumb-bytes');
		SizeVariant::query()
			->where('photo_id', '=', $this->photo1->id)
			->where('type', '=', SizeVariantType::SMALL)
			->delete();
		// small2x row exists but no bytes are put for it; small row is gone entirely.

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/small2x");

		$response->assertOk();
		self::assertSame('thumb-bytes', $response->streamedContent());
	}

	/**
	 * PhotoAssetController::fallback() exhausted-chain case: small2x, small
	 * and thumb all have DB rows but none has a file on disk, and thumb has
	 * no further fallback target → 404.
	 */
	public function testFallbackChainExhaustedReturnsNotFound(): void
	{
		// Every relevant row exists (factory default) but no bytes are put for any of them.

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/small2x");

		$response->assertNotFound();
	}

	/**
	 * S3-backed variants are redirected unconditionally: PhotoAssetController
	 * does not check S3 object existence before redirecting, and does not
	 * fall back to a smaller local variant just because one happens to
	 * exist — once the selected variant resolves to the S3 disk, serving it
	 * (or 404ing on a stale/missing object) is left to the client following
	 * the signed URL, not to Lychee.
	 */
	public function testS3PrimaryRedirectsWithoutFallingBackToLocalVariant(): void
	{
		$primary = $this->small2xVariantOf($this->photo1);
		$primary->storage_disk = StorageDiskType::S3;
		$primary->save();

		// A local fallback variant exists and has bytes, but must not be used.
		$this->putBytes($this->smallVariantOf($this->photo1), 'small-bytes');

		$aws_adapter = \Mockery::mock(AwsS3V3Adapter::class);
		$s3_disk = \Mockery::mock(FilesystemAdapter::class, function (MockInterface $mock) use ($aws_adapter, $primary): void {
			$mock->shouldReceive('getAdapter')->andReturn($aws_adapter);
			$mock->shouldReceive('temporaryUrl')
				->once()
				->with($primary->short_path, \Mockery::any())
				->andReturn('https://example-bucket.s3.amazonaws.com/primary-signed-url');
			$mock->shouldReceive('url')->andReturn('https://example-bucket.s3.amazonaws.com/incidental-thumb-url');
		});

		// Only the 's3' disk is faked; every other disk name (e.g. the
		// 'images' local disk, already faked in setUp()) must still resolve
		// through the real manager, so we capture it before mocking the
		// facade and delegate non-S3 calls to it directly.
		$real_manager = Storage::getFacadeRoot();
		Storage::partialMock();
		Storage::shouldReceive('disk')->andReturnUsing(
			fn (?string $name = null) => $name === StorageDiskType::S3->value ? $s3_disk : $real_manager->disk($name)
		);

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/small2x");

		$response->assertRedirect('https://example-bucket.s3.amazonaws.com/primary-signed-url');
	}

	/**
	 * A fallback candidate can be pinned to a different storage disk than
	 * the primary variant that failed. PhotoAssetController::fallback()
	 * must resolve that candidate's own disk rather than reusing the
	 * primary's, so an S3-backed fallback variant is still recognized and
	 * redirected correctly instead of being checked against the wrong disk.
	 */
	public function testFallbackVariantServedFromItsOwnDiskWhenDifferentFromPrimary(): void
	{
		// small2x row exists on the local disk (factory default) but no bytes are put for it.
		$fallback_variant = $this->smallVariantOf($this->photo1);
		$fallback_variant->storage_disk = StorageDiskType::S3;
		$fallback_variant->save();

		$aws_adapter = \Mockery::mock(AwsS3V3Adapter::class);
		$s3_disk = \Mockery::mock(FilesystemAdapter::class, function (MockInterface $mock) use ($aws_adapter, $fallback_variant): void {
			$mock->shouldReceive('getAdapter')->andReturn($aws_adapter);
			$mock->shouldReceive('temporaryUrl')
				->once()
				->with($fallback_variant->short_path, \Mockery::any())
				->andReturn('https://example-bucket.s3.amazonaws.com/fallback-signed-url');
			// Resolving album1 also eagerly loads its cover/thumb (Album::$with),
			// which may compute a URL for whichever photo/variant was picked as
			// the album's thumbnail — incidental to what this test asserts, but
			// still needs a stub since it can land on the S3 disk too.
			$mock->shouldReceive('url')->andReturn('https://example-bucket.s3.amazonaws.com/incidental-thumb-url');
		});

		$real_manager = Storage::getFacadeRoot();
		Storage::partialMock();
		Storage::shouldReceive('disk')->andReturnUsing(
			fn (?string $name = null) => $name === StorageDiskType::S3->value ? $s3_disk : $real_manager->disk($name)
		);

		$response = $this->actingAs($this->userMayUpload1)->getV3("Asset/{$this->album1->id}/{$this->photo1->id}/small2x");

		$response->assertRedirect('https://example-bucket.s3.amazonaws.com/fallback-signed-url');
	}
}
