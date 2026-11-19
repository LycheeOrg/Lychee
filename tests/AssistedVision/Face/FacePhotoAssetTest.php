<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\AssistedVision\Face;

use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Models\Configs;
use App\Models\Face;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Services\TemporaryLinkSigner;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Mockery\MockInterface;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * `GET /api/v3/Faces/{face_id}/photo/{variant}` (Feature 093, API-093-05, ADR-093-01).
 */
class FacePhotoAssetTest extends BaseApiWithDataTest
{
	private Face $face;

	public function setUp(): void
	{
		parent::setUp();
		Storage::fake(StorageDiskType::LOCAL->value);

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');
		Configs::set('temporary_image_link_enabled', '0');

		$this->face = Face::factory()->for_photo($this->photo1)->create();
		$this->putBytes($this->variantOf($this->photo1, SizeVariantType::MEDIUM), 'medium-bytes');
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		parent::tearDown();
	}

	private function variantOf(Photo $photo, SizeVariantType $type): SizeVariant
	{
		return SizeVariant::query()
			->where('photo_id', '=', $photo->id)
			->where('type', '=', $type)
			->firstOrFail();
	}

	private function putBytes(SizeVariant $variant, string $bytes): void
	{
		Storage::disk(StorageDiskType::LOCAL->value)->put($variant->short_path, $bytes);
	}

	private function url(?string $face_id = null, string $variant = 'medium'): string
	{
		return 'Faces/' . ($face_id ?? $this->face->id) . '/photo/' . $variant;
	}

	public function testOwnerGetsMedium(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getV3($this->url());
		$response->assertOk();
		self::assertSame('medium-bytes', $response->streamedContent());
	}

	public function testAdminGetsMedium(): void
	{
		$response = $this->actingAs($this->admin)->getV3($this->url());
		$response->assertOk();
		self::assertSame('medium-bytes', $response->streamedContent());
	}

	public function testOtherUserIsForbidden(): void
	{
		$this->actingAs($this->userMayUpload2)->getV3($this->url())->assertForbidden();
	}

	public function testGuestIsUnauthorized(): void
	{
		$this->getV3($this->url())->assertUnauthorized();
	}

	public function testFlagOffIsForbidden(): void
	{
		config(['features.v8' => false]);
		$this->actingAs($this->admin)->getV3($this->url())->assertForbidden();
	}

	public function testFaceRecognitionOffIsForbidden(): void
	{
		Configs::set('ai_vision_face_enabled', '0');
		$this->actingAs($this->admin)->getV3($this->url())->assertForbidden();
	}

	public function testUnknownFaceIsNotFound(): void
	{
		$this->actingAs($this->admin)->getV3($this->url('abcdefghijklmnopqrstuvwx'))->assertNotFound();
	}

	public function testInvalidVariantIsRejected(): void
	{
		$this->actingAs($this->admin)->getV3($this->url(null, 'original'))->assertUnprocessable();
	}

	public function testFallsBackWhenMediumIsMissing(): void
	{
		SizeVariant::query()->where('photo_id', '=', $this->photo1->id)
			->whereIn('type', [SizeVariantType::MEDIUM, SizeVariantType::SMALL2X])
			->delete();
		$this->putBytes($this->variantOf($this->photo1, SizeVariantType::SMALL), 'small-bytes');

		$response = $this->actingAs($this->userMayUpload1)->getV3($this->url());
		$response->assertOk();
		self::assertSame('small-bytes', $response->streamedContent());
	}

	public function testDismissedOrAssignedFaceStillServed(): void
	{
		DB::table('faces')->where('id', '=', $this->face->id)->update(['is_dismissed' => true]);
		$this->actingAs($this->userMayUpload1)->getV3($this->url())->assertOk();
	}

	public function testSignatureRequiredWhenTemporaryLinksEnabled(): void
	{
		Configs::set('temporary_image_link_enabled', '1');
		Configs::set('temporary_image_link_when_logged_in', '1');

		$this->actingAs($this->userMayUpload1)->getV3($this->url())->assertUnauthorized();

		$response = $this->actingAs($this->userMayUpload1)->getV3($this->url(), ['X-Mac' => (new TemporaryLinkSigner())->sign()]);
		$response->assertOk();
		self::assertSame('medium-bytes', $response->streamedContent());
	}

	/**
	 * Feature 102 (FR-102-04): the shared serving code makes the file
	 * privately cacheable.
	 */
	public function testServedFileIsCacheable(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getV3($this->url());

		$response->assertOk();
		self::assertStringContainsString('max-age=3600', (string) $response->headers->get('Cache-Control'));
		self::assertTrue($response->headers->has('Last-Modified'));
	}

	/**
	 * S-113-03 (Feature 113, FR-113-01): an S3 medium answers 200 JSON with
	 * S3's presigned URL, never a redirect the XHR would follow.
	 */
	public function testS3MediumAnswersWithPresignedUrl(): void
	{
		config(['filesystems.disks.s3.visibility' => 'private']);
		$variant = $this->variantOf($this->photo1, SizeVariantType::MEDIUM);
		$variant->storage_disk = StorageDiskType::S3;
		$variant->save();

		$aws_adapter = \Mockery::mock(AwsS3V3Adapter::class);
		$s3_disk = \Mockery::mock(FilesystemAdapter::class, function (MockInterface $mock) use ($aws_adapter, $variant): void {
			$mock->shouldReceive('getAdapter')->andReturn($aws_adapter);
			$mock->shouldReceive('temporaryUrl')
				->once()
				->with($variant->short_path, \Mockery::any())
				->andReturn('https://example-bucket.s3.amazonaws.com/signed-medium');
		});
		$real_manager = Storage::getFacadeRoot();
		Storage::partialMock();
		Storage::shouldReceive('disk')->andReturnUsing(
			fn (?string $name = null) => $name === StorageDiskType::S3->value ? $s3_disk : $real_manager->disk($name)
		);

		$response = $this->actingAs($this->userMayUpload1)->getV3($this->url());

		$response->assertOk();
		$response->assertExactJson(['url' => 'https://example-bucket.s3.amazonaws.com/signed-medium']);
		$response->assertHeaderMissing('Location');
	}
}
