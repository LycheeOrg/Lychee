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

namespace Tests\Unit\Actions\SizeVariant;

use App\Actions\SizeVariant\Delete;
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
	 * Test basic size variant deletion when not referenced in orders.
	 *
	 * Setup:
	 * - Photo with multiple size variants
	 * - None of the size variants are in orders
	 *
	 * When deleting size variants:
	 * - All specified size variants should be deleted
	 *
	 * @return void
	 */
	public function testDeleteSizeVariantsNotInOrders(): void
	{
		// Create a user for ownership
		$user = User::factory()->create();

		// Create photo with size variants
		$photo = Photo::factory()->owned_by($user)->create();

		// Get the size variants (created by factory)
		$size_variants = SizeVariant::where('photo_id', $photo->id)->get();
		$this->assertGreaterThan(0, $size_variants->count(), 'Photo should have size variants');

		$size_variant_ids = $size_variants->pluck('id')->all();

		// Verify initial state
		foreach ($size_variant_ids as $sv_id) {
			$this->assertDatabaseHas('size_variants', ['id' => $sv_id]);
		}

		// Execute deletion
		$delete_action = new Delete();
		$delete_action->do($size_variant_ids);

		// Verify all size variants are deleted
		foreach ($size_variant_ids as $sv_id) {
			$this->assertDatabaseMissing('size_variants', ['id' => $sv_id]);
		}
	}

	/**
	 * Test that size variants referenced in orders are preserved.
	 *
	 * Setup:
	 * - Photo with multiple size variants
	 * - One size variant has been purchased (order_item references it)
	 *
	 * When deleting all size variants:
	 * - Size variant referenced by order_item should still exist
	 * - Other size variants should be deleted
	 *
	 * @return void
	 */
	public function testDeleteSizeVariantsPreservesOrderReferences(): void
	{
		// Create a user for ownership
		$user = User::factory()->create();

		// Create photo with size variants
		$photo = Photo::factory()->owned_by($user)->create();

		// Get the size variants (created by factory)
		$size_variants = SizeVariant::where('photo_id', $photo->id)->get();
		$this->assertGreaterThan(0, $size_variants->count(), 'Photo should have size variants');

		// Pick one size variant to be "purchased"
		$purchased_size_variant = $size_variants->first();

		// Create an order with an item referencing this size variant
		$order = Order::factory()->create(['user_id' => $user->id]);
		OrderItem::factory()->create([
			'order_id' => $order->id,
			'photo_id' => $photo->id,
			'size_variant_id' => $purchased_size_variant->id,
			'title' => $photo->title,
		]);

		$size_variant_ids = $size_variants->pluck('id')->all();

		// Verify initial state
		$this->assertDatabaseHas('size_variants', ['id' => $purchased_size_variant->id]);
		$this->assertDatabaseHas('order_items', ['size_variant_id' => $purchased_size_variant->id]);

		// Execute deletion of all size variants
		$delete_action = new Delete();
		$delete_action->do($size_variant_ids);

		// Verify the purchased size variant still exists
		$this->assertDatabaseHas('size_variants', ['id' => $purchased_size_variant->id]);

		// Verify the order item still references the size variant
		$this->assertDatabaseHas('order_items', ['size_variant_id' => $purchased_size_variant->id]);

		// Verify other size variants are deleted
		$other_size_variant_ids = $size_variants->pluck('id')->reject(fn ($id) => $id === $purchased_size_variant->id);
		foreach ($other_size_variant_ids as $sv_id) {
			$this->assertDatabaseMissing('size_variants', ['id' => $sv_id]);
		}
	}

	/**
	 * Test that multiple size variants in different orders are preserved.
	 *
	 * Setup:
	 * - Photo with multiple size variants
	 * - Two different size variants have been purchased in different orders
	 *
	 * When deleting all size variants:
	 * - Both size variants referenced by orders should still exist
	 * - Other size variants should be deleted
	 *
	 * @return void
	 */
	public function testDeleteSizeVariantsPreservesMultipleOrderReferences(): void
	{
		// Create a user for ownership
		$user = User::factory()->create();

		// Create photo with size variants
		$photo = Photo::factory()->owned_by($user)->create();

		// Get the size variants
		$size_variants = SizeVariant::where('photo_id', $photo->id)->get();
		$this->assertGreaterThanOrEqual(2, $size_variants->count(), 'Photo should have at least 2 size variants');

		// Pick two size variants to be "purchased"
		$purchased_sv_1 = $size_variants->first();
		$purchased_sv_2 = $size_variants->skip(1)->first();

		// Create orders with items referencing these size variants
		$order1 = Order::factory()->create(['user_id' => $user->id]);
		OrderItem::factory()->create([
			'order_id' => $order1->id,
			'photo_id' => $photo->id,
			'size_variant_id' => $purchased_sv_1->id,
			'title' => $photo->title,
		]);

		$order2 = Order::factory()->create(['user_id' => $user->id]);
		OrderItem::factory()->create([
			'order_id' => $order2->id,
			'photo_id' => $photo->id,
			'size_variant_id' => $purchased_sv_2->id,
			'title' => $photo->title,
		]);

		$size_variant_ids = $size_variants->pluck('id')->all();

		// Execute deletion of all size variants
		$delete_action = new Delete();
		$delete_action->do($size_variant_ids);

		// Verify both purchased size variants still exist
		$this->assertDatabaseHas('size_variants', ['id' => $purchased_sv_1->id]);
		$this->assertDatabaseHas('size_variants', ['id' => $purchased_sv_2->id]);

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

	/**
	 * Test deleting size variants with mixed order references.
	 *
	 * Setup:
	 * - Two photos each with size variants
	 * - Photo A has one size variant in an order
	 * - Photo B has no size variants in orders
	 *
	 * When deleting all size variants from both photos:
	 * - Photo A's purchased size variant should be preserved
	 * - Photo A's other size variants should be deleted
	 * - All of Photo B's size variants should be deleted
	 *
	 * @return void
	 */
	public function testDeleteMixedSizeVariantsWithOrderReferences(): void
	{
		// Create a user for ownership
		$user = User::factory()->create();

		// Create two photos with size variants
		$photo_a = Photo::factory()->owned_by($user)->create();
		$photo_b = Photo::factory()->owned_by($user)->create();

		// Get size variants for both photos
		$size_variants_a = SizeVariant::where('photo_id', $photo_a->id)->get();
		$size_variants_b = SizeVariant::where('photo_id', $photo_b->id)->get();

		$this->assertGreaterThan(0, $size_variants_a->count(), 'Photo A should have size variants');
		$this->assertGreaterThan(0, $size_variants_b->count(), 'Photo B should have size variants');

		// Pick one size variant from photo A to be "purchased"
		$purchased_size_variant = $size_variants_a->first();

		// Create an order with an item referencing photo A's size variant
		$order = Order::factory()->create(['user_id' => $user->id]);
		OrderItem::factory()->create([
			'order_id' => $order->id,
			'photo_id' => $photo_a->id,
			'size_variant_id' => $purchased_size_variant->id,
			'title' => $photo_a->title,
		]);

		// Collect all size variant IDs from both photos
		$all_size_variant_ids = $size_variants_a->pluck('id')
			->merge($size_variants_b->pluck('id'))
			->all();

		// Execute deletion of all size variants
		$delete_action = new Delete();
		$delete_action->do($all_size_variant_ids);

		// Verify photo A's purchased size variant still exists
		$this->assertDatabaseHas('size_variants', ['id' => $purchased_size_variant->id]);

		// Verify other size variants from photo A are deleted
		$other_size_variant_ids_a = $size_variants_a->pluck('id')
			->reject(fn ($id) => $id === $purchased_size_variant->id);
		foreach ($other_size_variant_ids_a as $sv_id) {
			$this->assertDatabaseMissing('size_variants', ['id' => $sv_id]);
		}

		// Verify all size variants from photo B are deleted
		foreach ($size_variants_b->pluck('id') as $sv_id) {
			$this->assertDatabaseMissing('size_variants', ['id' => $sv_id]);
		}
	}
}
