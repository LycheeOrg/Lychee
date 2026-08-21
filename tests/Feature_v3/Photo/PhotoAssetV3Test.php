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

	private function putBytes(SizeVariant $variant, string $bytes = 'thumb-bytes'): void
	{
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, $bytes);
	}

	/**
	 * @return array<string,string>
	 */
	private function signedHeaders(int $timestamp): array
	{
		$signer = new TemporaryLinkSigner();

		return [
			'X-Timestamp' => (string) $timestamp,
			'X-Mac' => $signer->sign($timestamp),
		];
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

		$response = $this->actingAs($this->userMayUpload1)->getV3("Photo/{$this->photo1->id}/Asset/thumb");

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

		$response = $this->getV3("Photo/{$this->photo4->id}/Asset/thumb", $this->signedHeaders(now()->timestamp));

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

		$response = $this->getV3("Photo/{$this->photo1->id}/Asset/thumb", $this->signedHeaders(now()->timestamp));

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

		$response = $this->getV3("Photo/{$this->photo4->id}/Asset/thumb");

		$response->assertUnauthorized();
	}

	/**
	 * S-056-05: temporary_image_link_enabled=false → 401 regardless of
	 * (validly-signed) headers.
	 */
	public function testDisabledFeatureIsUnauthorizedForGuest(): void
	{
		Configs::set('temporary_image_link_enabled', '0');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$response = $this->getV3("Photo/{$this->photo4->id}/Asset/thumb", $this->signedHeaders(now()->timestamp));

		$response->assertUnauthorized();
	}

	/**
	 * S-056-06: X-Mac that doesn't match the HMAC of X-Timestamp → 401.
	 */
	public function testTamperedMacIsUnauthorized(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$headers = $this->signedHeaders(now()->timestamp);
		$headers['X-Mac'] = substr($headers['X-Mac'], 0, -1) . (str_ends_with($headers['X-Mac'], 'a') ? 'b' : 'a');

		$response = $this->getV3("Photo/{$this->photo4->id}/Asset/thumb", $headers);

		$response->assertUnauthorized();
	}

	/**
	 * S-056-07: X-Timestamp older than
	 * now() - temporary_image_link_life_in_seconds → 401 (expired).
	 */
	public function testExpiredTimestampIsUnauthorized(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$life = resolve(ConfigManager::class)->getValueAsInt('temporary_image_link_life_in_seconds');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$response = $this->getV3("Photo/{$this->photo4->id}/Asset/thumb", $this->signedHeaders(now()->timestamp - $life - 60));

		$response->assertUnauthorized();
	}

	/**
	 * S-056-08: X-Timestamp in the future (> now()) → 401.
	 */
	public function testFutureTimestampIsUnauthorized(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$response = $this->getV3("Photo/{$this->photo4->id}/Asset/thumb", $this->signedHeaders(now()->timestamp + 60));

		$response->assertUnauthorized();
	}

	/**
	 * S-056-09: Only one of X-Timestamp/X-Mac present → 422.
	 */
	public function testOnlyOneHeaderPresentIsUnprocessable(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		$variant = $this->thumbVariantOf($this->photo4);
		$this->putBytes($variant);

		$response = $this->getV3("Photo/{$this->photo4->id}/Asset/thumb", ['X-Timestamp' => (string) now()->timestamp]);

		$response->assertUnprocessable();
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

		$response = $this->actingAs($this->userMayUpload1)->getV3("Photo/{$this->photo1->id}/Asset/thumb");

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

		$response = $this->actingAs($this->admin)->getV3("Photo/{$this->photo1->id}/Asset/thumb");

		$response->assertOk();
	}

	/**
	 * S-056-12: Request for size_variant=RAW on a photo with no stored RAW
	 * variant → 404 (photo1's factory-created variants never include RAW).
	 */
	public function testMissingSizeVariantRowReturnsNotFound(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getV3("Photo/{$this->photo1->id}/Asset/raw");

		$response->assertNotFound();
	}

	/**
	 * S-056-13: Unrecognized size_variant token → 422.
	 */
	public function testUnrecognizedSizeVariantTokenReturnsUnprocessable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getV3("Photo/{$this->photo1->id}/Asset/huge");

		$response->assertUnprocessable();
	}

	/**
	 * S-056-14: Unknown photo_id → 404.
	 */
	public function testUnknownPhotoIdReturnsNotFound(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getV3('Photo/000000000000000000000000/Asset/thumb');

		$response->assertNotFound();
	}

	/**
	 * S-056-15: Authorized request for a size_variant stored on the S3 disk
	 * → 302 redirect to a native S3 temporary URL, no bytes proxied through
	 * Lychee.
	 */
	public function testS3BackedVariantRedirectsToTemporaryUrl(): void
	{
		$variant = SizeVariant::query()
			->where('photo_id', '=', $this->photo1->id)
			->where('type', '=', SizeVariantType::MEDIUM)
			->firstOrFail();
		$variant->storage_disk = StorageDiskType::S3;
		$variant->save();

		$aws_adapter = \Mockery::mock(AwsS3V3Adapter::class);
		$s3_disk = \Mockery::mock(FilesystemAdapter::class, function (MockInterface $mock) use ($aws_adapter, $variant): void {
			$mock->shouldReceive('getAdapter')->andReturn($aws_adapter);
			$mock->shouldReceive('temporaryUrl')
				->once()
				->with($variant->short_path, \Mockery::any())
				->andReturn('https://example-bucket.s3.amazonaws.com/signed-url');
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

		$response = $this->actingAs($this->userMayUpload1)->getV3("Photo/{$this->photo1->id}/Asset/medium");

		$response->assertRedirect('https://example-bucket.s3.amazonaws.com/signed-url');
	}

	/**
	 * S-056-16: Authorized request for size_variant=MEDIUM where the
	 * requesting viewer meets watermark conditions → served file is the
	 * watermarked path, not the plain stored path.
	 */
	public function testWatermarkedPathServedWhenConditionsMet(): void
	{
		Configs::set('watermark_enabled', '1');
		Configs::set('watermark_logged_in_users_enabled', '1');

		$variant = SizeVariant::query()
			->where('photo_id', '=', $this->photo1->id)
			->where('type', '=', SizeVariantType::MEDIUM)
			->firstOrFail();
		$variant->short_path_watermarked = 'watermarked/' . $variant->short_path;
		$variant->save();

		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, 'plain-bytes');
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path_watermarked, 'watermarked-bytes');

		$response = $this->actingAs($this->userMayUpload1)->getV3("Photo/{$this->photo1->id}/Asset/medium");

		$response->assertOk();
		self::assertSame('watermarked-bytes', $response->streamedContent());
	}

	/**
	 * NFR-056-04: Full signatureRequired() config/caller-state matrix.
	 *
	 * Enumerates all 2×2×2 combinations of temporary_image_link_enabled /
	 * _when_logged_in / _when_admin, crossed with (guest / logged-in
	 * non-admin / admin) caller state, all requesting without any
	 * X-Timestamp/X-Mac headers. Truth table (several combinations collapse
	 * to the same outcome):
	 *
	 * - Guest: always 401, regardless of any config flag — guests are only
	 *   ever authorized via a valid temporary link (FR-056-05), and no
	 *   headers means that link can never be valid.
	 * - Logged-in non-admin: 401 iff (enabled && when_logged_in), else 200
	 *   (session alone suffices). `when_admin` is irrelevant to this caller.
	 * - Admin: 401 iff (enabled && when_admin), else 200 (session alone
	 *   suffices, admin bypasses PhotoPolicy too). `when_logged_in` is
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
					$guest_response = $this->getV3("Photo/{$this->photo4->id}/Asset/thumb");
					self::assertSame(401, $guest_response->getStatusCode(), "guest, {$case}");

					$non_admin_response = $this->actingAs($this->userMayUpload1)->getV3("Photo/{$this->photo1->id}/Asset/thumb");
					$expected_non_admin = ($enabled && $when_logged_in) ? 401 : 200;
					self::assertSame($expected_non_admin, $non_admin_response->getStatusCode(), "non-admin, {$case}");

					$admin_response = $this->actingAs($this->admin)->getV3("Photo/{$this->photo1->id}/Asset/thumb");
					$expected_admin = ($enabled && $when_admin) ? 401 : 200;
					self::assertSame($expected_admin, $admin_response->getStatusCode(), "admin, {$case}");
				}
			}
		}
	}

	/**
	 * S-056-17: The same photo/session returns 200 for THUMB (CAN_SEE) but
	 * 403 for ORIGINAL (CAN_ACCESS_FULL_PHOTO) when the album disables
	 * full-resolution access.
	 */
	public function testCanSeeVsCanAccessFullPhotoSplit(): void
	{
		$this->putBytes($this->thumbVariantOf($this->photo1));
		$original = SizeVariant::query()
			->where('photo_id', '=', $this->photo1->id)
			->where('type', '=', SizeVariantType::ORIGINAL)
			->firstOrFail();
		$this->putBytes($original, 'original-bytes');

		// perm1 grants userMayUpload2 view access to album1 but not full-photo access.
		$this->perm1->update(['grants_full_photo_access' => false]);

		$thumb_response = $this->actingAs($this->userMayUpload2)->getV3("Photo/{$this->photo1->id}/Asset/thumb");
		$thumb_response->assertOk();

		$original_response = $this->actingAs($this->userMayUpload2)->getV3("Photo/{$this->photo1->id}/Asset/original");
		$original_response->assertForbidden();
	}
}
