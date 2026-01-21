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

namespace Tests\Unit\Actions\Photo;

use App\Actions\Photo\Delete;
use App\Models\Album;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class DeleteTest extends AbstractTestCase
{
	use DatabaseTransactions;

	/**
	 * Test deleting photos with shared photos across albums.
	 *
	 * Setup:
	 * - 2 photos: A, B
	 * - 2 albums: X, Y
	 * - Photo A is in both X and Y
	 * - Photo B is only in X
	 *
	 * When deleting photos A and B from album X:
	 * - X album should still exist
	 * - Y album should still exist
	 * - A should still exist (still in Y)
	 * - B should be deleted (only in X)
	 *
	 * @return void
	 */
	public function testDeletePhotosWithSharedPhotos(): void
	{
		// Create a user for ownership
		$user = User::factory()->create();

		// Create photos
		$photo_a = Photo::factory()->owned_by($user)->create();
		$photo_b = Photo::factory()->owned_by($user)->create();

		// Create albums
		$album_x = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Album X']);
		$album_y = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Album Y']);

		// Attach photos to albums
		// A and B are in X
		$photo_a->albums()->attach($album_x->id);
		$photo_b->albums()->attach($album_x->id);

		// A is also in Y
		$photo_a->albums()->attach($album_y->id);

		// Verify initial state
		$this->assertDatabaseHas('albums', ['id' => $album_x->id]);
		$this->assertDatabaseHas('albums', ['id' => $album_y->id]);
		$this->assertDatabaseHas('photos', ['id' => $photo_a->id]);
		$this->assertDatabaseHas('photos', ['id' => $photo_b->id]);

		// Execute deletion of photos A and B from album X
		$delete_action = new Delete();
		$delete_action->do([$photo_a->id, $photo_b->id], $album_x->id);

		// Verify X album still exists
		$this->assertDatabaseHas('albums', ['id' => $album_x->id]);

		// Verify Y album still exists
		$this->assertDatabaseHas('albums', ['id' => $album_y->id]);

		// Verify photo A still exists (because it's also in Y)
		$this->assertDatabaseHas('photos', ['id' => $photo_a->id]);

		// Verify photo B is deleted (only in X)
		$this->assertDatabaseMissing('photos', ['id' => $photo_b->id]);

		// Verify A is still in Y
		$this->assertDatabaseHas('photo_album', [
			'album_id' => $album_y->id,
			'photo_id' => $photo_a->id,
		]);

		// Verify A is no longer in X
		$this->assertDatabaseMissing('photo_album', [
			'album_id' => $album_x->id,
			'photo_id' => $photo_a->id,
		]);

		// Verify B is no longer in X
		$this->assertDatabaseMissing('photo_album', [
			'album_id' => $album_x->id,
			'photo_id' => $photo_b->id,
		]);
	}

	/**
	 * Test that size variants referenced in orders are preserved when photo is deleted.
	 *
	 * Setup:
	 * - Photo A with size variants in album X
	 * - Photo A has been purchased (order_item references one of its size variants)
	 *
	 * When deleting photo A from album X:
	 * - Photo A should be deleted
	 * - Size variant referenced by order_item should still exist
	 * - Size variant's photo_id should be set to null
	 * - Other size variants of photo A should be deleted
	 *
	 * @return void
	 */
	public function testDeletePhotoPreservesSizeVariantsInOrders(): void
	{
		// Create a user for ownership
		$user = User::factory()->create();

		// Create photo with size variants
		$photo_a = Photo::factory()->owned_by($user)->create();

		// Create album
		$album_x = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Album X']);

		// Attach photo to album
		$photo_a->albums()->attach($album_x->id);

		// Get the size variants (created by factory)
		$size_variants = SizeVariant::where('photo_id', $photo_a->id)->get();
		$this->assertGreaterThan(0, $size_variants->count(), 'Photo should have size variants');

		// Pick one size variant to be "purchased"
		$purchased_size_variant = $size_variants->first();

		// Create an order with an item referencing this size variant
		$order = Order::factory()->create(['user_id' => $user->id]);
		OrderItem::factory()->create([
			'order_id' => $order->id,
			'photo_id' => $photo_a->id,
			'size_variant_id' => $purchased_size_variant->id,
			'title' => $photo_a->title,
		]);

		// Verify initial state
		$this->assertDatabaseHas('photos', ['id' => $photo_a->id]);
		$this->assertDatabaseHas('size_variants', ['id' => $purchased_size_variant->id, 'photo_id' => $photo_a->id]);
		$this->assertDatabaseHas('order_items', ['size_variant_id' => $purchased_size_variant->id]);

		// Execute deletion of photo A from album X
		$delete_action = new Delete();
		$delete_action->do([$photo_a->id], $album_x->id);

		// Verify photo A is deleted
		$this->assertDatabaseMissing('photos', ['id' => $photo_a->id]);

		// Verify the purchased size variant still exists but with null photo_id
		$this->assertDatabaseHas('size_variants', [
			'id' => $purchased_size_variant->id,
			'photo_id' => null,
		]);

		// Verify the order item still references the size variant
		$this->assertDatabaseHas('order_items', [
			'size_variant_id' => $purchased_size_variant->id,
			'photo_id' => null, // Order item's photo_id is set to null (onDelete set null)
		]);

		// Verify other size variants are deleted
		$other_size_variant_ids = $size_variants->pluck('id')->reject(fn ($id) => $id === $purchased_size_variant->id);
		foreach ($other_size_variant_ids as $sv_id) {
			$this->assertDatabaseMissing('size_variants', ['id' => $sv_id]);
		}
	}

	/**
	 * Test that multiple size variants in orders are preserved when photo is deleted.
	 *
	 * Setup:
	 * - Photo A with multiple size variants in album X
	 * - Two different size variants have been purchased in different orders
	 *
	 * When deleting photo A:
	 * - Photo A should be deleted
	 * - Both size variants referenced by orders should still exist with null photo_id
	 * - Other size variants should be deleted
	 *
	 * @return void
	 */
	public function testDeletePhotoPreservesMultipleSizeVariantsInOrders(): void
	{
		// Create a user for ownership
		$user = User::factory()->create();

		// Create photo with size variants
		$photo_a = Photo::factory()->owned_by($user)->create();

		// Create album
		$album_x = Album::factory()->as_root()->owned_by($user)->create(['title' => 'Album X']);

		// Attach photo to album
		$photo_a->albums()->attach($album_x->id);

		// Get the size variants
		$size_variants = SizeVariant::where('photo_id', $photo_a->id)->get();
		$this->assertGreaterThanOrEqual(2, $size_variants->count(), 'Photo should have at least 2 size variants');

		// Pick two size variants to be "purchased"
		$purchased_sv_1 = $size_variants->first();
		$purchased_sv_2 = $size_variants->skip(1)->first();

		// Create orders with items referencing these size variants
		$order1 = Order::factory()->create(['user_id' => $user->id]);
		OrderItem::factory()->create([
			'order_id' => $order1->id,
			'photo_id' => $photo_a->id,
			'size_variant_id' => $purchased_sv_1->id,
			'title' => $photo_a->title,
		]);

		$order2 = Order::factory()->create(['user_id' => $user->id]);
		OrderItem::factory()->create([
			'order_id' => $order2->id,
			'photo_id' => $photo_a->id,
			'size_variant_id' => $purchased_sv_2->id,
			'title' => $photo_a->title,
		]);

		// Execute deletion of photo A from album X
		$delete_action = new Delete();
		$delete_action->do([$photo_a->id], $album_x->id);

		// Verify photo A is deleted
		$this->assertDatabaseMissing('photos', ['id' => $photo_a->id]);

		// Verify both purchased size variants still exist with null photo_id
		$this->assertDatabaseHas('size_variants', [
			'id' => $purchased_sv_1->id,
			'photo_id' => null,
		]);
		$this->assertDatabaseHas('size_variants', [
			'id' => $purchased_sv_2->id,
			'photo_id' => null,
		]);

		// Verify order items still reference the size variants
		$this->assertDatabaseHas('order_items', ['size_variant_id' => $purchased_sv_1->id]);
		$this->assertDatabaseHas('order_items', ['size_variant_id' => $purchased_sv_2->id]);

		// Verify other size variants are deleted
		$other_size_variant_ids = $size_variants->pluck('id')
			->reject(fn ($id) => $id === $purchased_sv_1->id || $id === $purchased_sv_2->id);
		foreach ($other_size_variant_ids as $sv_id) {
			$this->assertDatabaseMissing('size_variants', ['id' => $sv_id]);
		}
	}
}
