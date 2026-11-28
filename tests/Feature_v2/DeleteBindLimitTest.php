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

use App\Actions\SizeVariant\Delete as SizeVariantDelete;
use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Enum\SizeVariantType;
use App\Models\Album;
use App\Models\Order;
use App\Models\TagAlbum;
use App\Models\Webhook;
use Illuminate\Database\Events\QueryExecuted;
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
 * - S-112-23: a size-variant delete that fails in its second chunk leaves every row in place.
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
		self::assertSame(0, $this->countIn('photos', 'id', $photo_ids));
		self::assertSame(0, DB::table('purchasables')->where('album_id', '=', $this->album1->id)->count());
		// The sold variants are kept, detached, and still referenced.
		self::assertCount(1200, $sold_ids);
		self::assertSame(1200, $this->countIn('size_variants', 'id', $sold_ids, fn ($query) => $query->whereNull('photo_id')));
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
		self::assertSame(0, $this->countIn('albums', 'id', $children));
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
		self::assertSame(0, $this->countIn('base_albums', 'id', $album_ids));
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
		self::assertSame(0, $this->countIn('base_albums', 'id', $album_ids));
	}

	public function testSizeVariantDeleteFailingInItsSecondChunkLeavesEveryRow(): void
	{
		$photo_ids = $this->bulkPhotos($this->userMayUpload1, 700, [$this->album1->id]);
		$sv_ids = [];
		foreach (array_chunk($photo_ids, 500) as $chunk) {
			$sv_ids = array_merge($sv_ids, DB::table('size_variants')->whereIn('photo_id', $chunk)->pluck('id')->all());
		}
		self::assertGreaterThan(500, count($sv_ids));

		// The second chunk's delete runs, then fails: every driver, no trigger.
		$deletes = 0;
		DB::listen(function (QueryExecuted $query) use (&$deletes): void {
			if (preg_match('/^delete from [`"]?size_variants[`"]?/i', $query->sql) === 1 && ++$deletes === 2) {
				throw new \RuntimeException('second chunk failed');
			}
		});

		try {
			(new SizeVariantDelete())->do($sv_ids);
			self::fail('The delete should have failed in its second chunk.');
		} catch (\RuntimeException $e) {
			self::assertSame('second chunk failed', $e->getMessage());
		}

		self::assertSame(2, $deletes);
		self::assertSame(count($sv_ids), $this->countIn('size_variants', 'id', $sv_ids));
	}

	/**
	 * Rows of $table whose $column is in $ids, counted in chunks of 500.
	 *
	 * @param array<int,int|string>                                     $ids
	 * @param (\Closure(\Illuminate\Database\Query\Builder):mixed)|null $scope
	 */
	private function countIn(string $table, string $column, array $ids, ?\Closure $scope = null): int
	{
		$count = 0;
		foreach (array_chunk($ids, 500) as $chunk) {
			$query = DB::table($table)->whereIn($column, $chunk);
			if ($scope !== null) {
				$scope($query);
			}
			$count += $query->count();
		}

		return $count;
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
