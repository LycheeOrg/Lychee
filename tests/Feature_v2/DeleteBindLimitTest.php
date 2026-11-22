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

namespace Tests\Feature_v2;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Enum\SizeVariantType;
use App\Models\Album;
use App\Models\Order;
use App\Models\TagAlbum;
use App\Models\Webhook;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\CreatesBulkFixtures;

/**
 * FR-112-09, NFR-112-04: no query of a delete request binds more than 1000
 * parameters (MySQL fails above that), permission checks included.
 *
 * Scenarios covered:
 * - S-112-10: 1200 selected photos with purchasables and 1200 sold size variants.
 * - S-112-11: an album whose subtree holds more than 1000 albums and photos.
 * - S-112-12: more than 1000 album ids, regular and tag albums, user and admin endpoints.
 */
class DeleteBindLimitTest extends BaseApiWithDataTest
{
	use CreatesBulkFixtures;

	public function testDelete1200SelectedPhotosWithPurchasablesAndSales(): void
	{
		// Delete webhooks on: PhotoWillBeDeleted is built for every photo.
		Config::set('features.webhook', true);
		Webhook::factory()->onPhotoDelete()->create();
		Http::fake();

		$photo_ids = $this->bulkPhotos($this->userMayUpload1, 1200, [$this->album1->id]);
		$this->bulkPurchasables($this->album1->id, $photo_ids);
		$sold_ids = $this->bulkSales($photo_ids);

		$this->listenToBindings();
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', [
			'photo_ids' => $photo_ids,
			'from_id' => $this->album1->id,
		]);
		$this->assertNoContent($response);

		$this->assertBindingsWithinLimit();
		self::assertSame(0, DB::table('photos')->whereIn('id', array_slice($photo_ids, 0, 500))->count());
		self::assertSame(0, DB::table('purchasables')->where('album_id', '=', $this->album1->id)->count());
		// The sold variants are kept, detached, and still referenced.
		self::assertCount(1200, $sold_ids);
		self::assertSame(500, DB::table('size_variants')->whereIn('id', array_slice($sold_ids, 0, 500))->whereNull('photo_id')->count());
		self::assertSame(1200, DB::table('order_items')->whereNotNull('size_variant_id')->count());
	}

	public function testDeleteAlbumWithSubtreeOfMoreThan1000AlbumsAndPhotos(): void
	{
		$root = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$children = $this->bulkChildAlbums($root, $this->userMayUpload1, 1100);
		$this->bulkPhotosIn($this->userMayUpload1, $children);
		// Linked to two sub-albums which fall in different chunks of 500.
		$shared = $this->bulkPhotos($this->userMayUpload1, 1, [$children[10], $children[1050]])[0];
		// A photo also linked outside the subtree is only detached.
		$kept = $this->bulkPhotos($this->userMayUpload1, 1, [$children[20], $this->album5->id])[0];

		$this->listenToBindings();
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Album', [
			'album_ids' => [$root->id],
		]);
		$this->assertNoContent($response);

		$this->assertBindingsWithinLimit();
		$this->assertDatabaseMissing('albums', ['id' => $root->id]);
		self::assertSame(0, DB::table('albums')->whereIn('id', array_slice($children, 0, 500))->count());
		$this->assertDatabaseMissing('photos', ['id' => $shared]);
		$this->assertDatabaseHas('photos', ['id' => $kept]);
		$this->assertDatabaseHas('photo_album', ['photo_id' => $kept, 'album_id' => $this->album5->id]);
	}

	public function testDeleteMoreThan1000RegularAndTagAlbums(): void
	{
		$album_ids = $this->mixedAlbumIds();

		$this->listenToBindings();
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Album', [
			'album_ids' => $album_ids,
		]);
		$this->assertNoContent($response);

		$this->assertBindingsWithinLimit();
		self::assertSame(0, DB::table('base_albums')->whereIn('id', array_slice($album_ids, 0, 500))->count());
		self::assertSame(0, DB::table('base_albums')->whereIn('id', array_slice($album_ids, 700, 500))->count());
	}

	public function testAdminBulkDeleteOfMoreThan1000RegularAndTagAlbums(): void
	{
		$album_ids = $this->mixedAlbumIds();

		$this->listenToBindings();
		$response = $this->actingAs($this->admin)->deleteJson('BulkAlbumEdit', [
			'album_ids' => $album_ids,
		]);
		$this->assertNoContent($response);

		$this->assertBindingsWithinLimit();
		self::assertSame(0, DB::table('base_albums')->whereIn('id', array_slice($album_ids, 0, 500))->count());
		self::assertSame(0, DB::table('base_albums')->whereIn('id', array_slice($album_ids, 700, 500))->count());
	}

	/**
	 * 600 sub-albums of album1 (each with one photo) followed by 600 tag albums,
	 * all owned by userMayUpload1.
	 *
	 * @return string[]
	 */
	private function mixedAlbumIds(): array
	{
		$regular = $this->bulkChildAlbums($this->album1, $this->userMayUpload1, 600);
		$this->bulkPhotosIn($this->userMayUpload1, $regular);
		$tag = $this->bulkTagAlbums(TagAlbum::factory()->owned_by($this->userMayUpload1)->of_tags([$this->tag_test])->create(), $this->userMayUpload1, 600);

		return [...$regular, ...$tag];
	}

	/**
	 * One active purchasable with a price per photo.
	 *
	 * @param string[] $photo_ids
	 */
	private function bulkPurchasables(string $album_id, array $photo_ids): void
	{
		foreach (array_chunk($photo_ids, 100) as $chunk) {
			DB::table('purchasables')->insert(array_map(fn (string $photo_id) => ['album_id' => $album_id, 'photo_id' => $photo_id, 'is_active' => true], $chunk));
		}
		$purchasable_ids = DB::table('purchasables')->where('album_id', '=', $album_id)->pluck('id')->all();
		foreach (array_chunk($purchasable_ids, 100) as $chunk) {
			DB::table('purchasable_prices')->insert(array_map(fn (int $id) => [
				'purchasable_id' => $id,
				'size_variant' => PurchasableSizeVariantType::ORIGINAL->value,
				'license_type' => PurchasableLicenseType::PERSONAL->value,
				'price_cents' => 500,
			], $chunk));
		}
	}

	/**
	 * One order item per photo, pointing at its ORIGINAL size variant.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return int[] the sold size variant ids
	 */
	private function bulkSales(array $photo_ids): array
	{
		$order = Order::factory()->create(['user_id' => $this->userMayUpload2->id]);
		$sold = [];
		foreach (array_chunk($photo_ids, 500) as $chunk) {
			$sold += DB::table('size_variants')->whereIn('photo_id', $chunk)->where('type', '=', SizeVariantType::ORIGINAL->value)->pluck('id', 'photo_id')->all();
		}
		$items = [];
		foreach ($sold as $photo_id => $size_variant_id) {
			$items[] = [
				'order_id' => $order->id,
				'photo_id' => $photo_id,
				'size_variant_id' => $size_variant_id,
				'title' => 'sold',
				'license_type' => PurchasableLicenseType::PERSONAL->value,
				'price_cents' => 500,
				'size_variant_type' => PurchasableSizeVariantType::ORIGINAL->value,
			];
		}
		foreach (array_chunk($items, 50) as $chunk) {
			DB::table('order_items')->insert($chunk);
		}

		return array_values($sold);
	}
}
