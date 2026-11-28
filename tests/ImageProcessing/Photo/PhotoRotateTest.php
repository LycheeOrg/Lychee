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

namespace Tests\ImageProcessing\Photo;

use App\Enum\SizeVariantType;
use App\Models\Configs;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SizeVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Constants\TestConstants;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequiresImageHandler;

class PhotoRotateTest extends BaseApiWithDataTest
{
	use RequiresImageHandler;

	public function testRotatePhotoForbidden(): void
	{
		$this->setUpRequiresImagick();

		$response = $this->postJson('Photo::rotate', []);
		$this->assertUnprocessable($response);

		$response = $this->postJson('Photo::rotate', [
			'photo_id' => $this->photo1->id,
			'direction' => 1,
			'from_id' => $this->album1->id,
		]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->userNoUpload)->postJson('Photo::rotate', [
			'photo_id' => $this->photo1->id,
			'direction' => 1,
			'from_id' => $this->album1->id,
		]);
		$this->assertForbidden($response);

		Configs::set('editor_enabled', false);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::rotate', [
			'photo_id' => $this->photo1->id,
			'direction' => 1,
			'from_id' => $this->album1->id,
		]);
		$this->assertStatus($response, 412);

		$this->tearDownRequiresImageHandler();
	}

	public function testRotatePhotoAuthorizeGd(): void
	{
		$this->setUpRequiresGD();

		Configs::set('editor_enabled', true);

		$this->rotate();

		Configs::set('editor_enabled', false);

		$this->tearDownRequiresImageHandler();
	}

	public function testRotatePhotoAuthorizeInagick(): void
	{
		$this->setUpRequiresImagick();
		Configs::set('editor_enabled', true);

		$this->rotate();

		Configs::set('editor_enabled', false);

		$this->tearDownRequiresImageHandler();
	}

	/**
	 * S-112-20: rotating a photo whose original is sold works. The photo gets
	 * a new original; the sold one keeps its row and file, detached, and the
	 * order item still points at it.
	 */
	public function testRotatePhotoWithSoldOriginal(): void
	{
		$this->setUpRequiresGD();
		Configs::set('editor_enabled', true);

		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', album_id: $this->album1->id, filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);
		$photo_id = DB::table('photo_album')
			->where('album_id', '=', $this->album1->id)
			->whereNotIn('photo_id', [$this->photo1->id, $this->photo1b->id])
			->value('photo_id');
		self::assertNotNull($photo_id);

		/** @var SizeVariant $sold */
		$sold = SizeVariant::query()->where('photo_id', $photo_id)->where('type', SizeVariantType::ORIGINAL)->firstOrFail();
		$order = Order::factory()->create(['user_id' => $this->userMayUpload2->id]);
		OrderItem::factory()->create([
			'order_id' => $order->id,
			'photo_id' => $photo_id,
			'size_variant_id' => $sold->id,
			'title' => 'sold',
		]);

		try {
			$response = $this->actingAs($this->userMayUpload1)->postJson('Photo::rotate', [
				'photo_id' => $photo_id,
				'direction' => 1,
				'from_id' => $this->album1->id,
			]);
			$this->assertStatus($response, 201);

			$new_original = SizeVariant::query()->where('photo_id', $photo_id)->where('type', SizeVariantType::ORIGINAL)->first();
			self::assertNotNull($new_original);
			self::assertNotSame($sold->id, $new_original->id);
			$this->assertDatabaseHas('size_variants', ['id' => $sold->id, 'photo_id' => null]);
			$this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'size_variant_id' => $sold->id]);
			self::assertTrue(Storage::disk($sold->storage_disk->value)->exists($sold->short_path));
		} finally {
			Storage::disk($sold->storage_disk->value)->delete($sold->short_path);
			Configs::set('editor_enabled', false);
			$this->tearDownRequiresImageHandler();
		}
	}

	private function rotate(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Album', [
			'parent_id' => $this->subAlbum1->id,
			'title' => 'test',
		]);
		self::assertEquals(200, $response->getStatusCode());
		$new_album_id = $response->getOriginalContent();

		$response = $this->actingAs($this->userMayUpload1)->upload('Photo', album_id: $new_album_id, filename: TestConstants::SAMPLE_FILE_NIGHT_IMAGE);
		$this->assertCreated($response);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Sharing', [
			'user_ids' => [$this->userMayUpload2->id],
			'group_ids' => [],
			'album_ids' => [$new_album_id],
			'grants_edit' => true,
			'grants_move' => true,
			'grants_delete' => true,
			'grants_download' => true,
			'grants_full_photo_access' => true,
			'grants_upload' => true,
		]);
		$this->assertOk($response);

		$response = $this->actingAs($this->userMayUpload2)->getJsonWithData('Album::photos', ['album_id' => $new_album_id]);
		$this->assertOk($response);
		$id1 = $response->json('photos.0.id');

		$response = $this->actingAs($this->userMayUpload2)->postJson('Photo::rotate', [
			'photo_id' => $id1,
			'direction' => 1,
			'from_id' => $new_album_id,
		]);
		$this->assertStatus($response, 201);
	}
}
