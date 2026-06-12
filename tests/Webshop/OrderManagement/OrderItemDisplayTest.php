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

namespace Tests\Webshop\OrderManagement;

use App\Enum\OmnipayProviderType;
use App\Enum\PaymentStatusType;
use App\Enum\SizeVariantType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Support\Str;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequirePro;

/**
 * Tests for Feature 042 – Photo Display Enrichment on order item detail page.
 *
 * Covers T-042-03 through T-042-06:
 *  - T-042-03: Happy path — both album_title and thumb_url are non-null.
 *  - T-042-04: album_title is null when the linked album is absent.
 *  - T-042-05: thumb_url is null when the linked photo is absent.
 *  - T-042-06: thumb_url is null when the photo has no THUMB size variant.
 */
class OrderItemDisplayTest extends BaseApiWithDataTest
{
	use RequirePro;

	public function setUp(): void
	{
		parent::setUp();
		$this->requirePro();
	}

	public function tearDown(): void
	{
		$this->resetPro();
		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// T-042-03: Happy path — both album_title and thumb_url non-null (S-042-06, S-042-07)
	// -------------------------------------------------------------------------

	public function testAlbumTitleAndThumbUrlPresentWhenPhotoAndAlbumExist(): void
	{
		// $this->photo1 is in $this->album1 and has all 7 size variants (incl. THUMB).
		$order = Order::factory()
			->forUser($this->userMayUpload1)
			->withTransactionId(Str::uuid()->toString())
			->withProvider(OmnipayProviderType::DUMMY)
			->withStatus(PaymentStatusType::PENDING)
			->withEmail($this->userMayUpload1->email)
			->create();

		OrderItem::factory()
			->forOrder($order)
			->forPhoto($this->photo1)
			->forAlbum($this->album1)
			->create();

		$response = $this->actingAs($this->userMayUpload1)->getJson("Shop/Order/{$order->id}");

		$this->assertOk($response);
		$items = $response->json('items');
		$this->assertIsArray($items);
		$this->assertCount(1, $items);

		$item = $items[0];
		$this->assertEquals($this->album1->title, $item['album_title']);
		$this->assertNotNull($item['thumb_url']);
		$this->assertIsString($item['thumb_url']);
	}

	// -------------------------------------------------------------------------
	// T-042-04: album_title is null when album deleted (S-042-02)
	// -------------------------------------------------------------------------

	public function testAlbumTitleIsNullWhenAlbumIsAbsent(): void
	{
		$order = Order::factory()
			->forUser($this->userMayUpload1)
			->withTransactionId(Str::uuid()->toString())
			->withProvider(OmnipayProviderType::DUMMY)
			->withStatus(PaymentStatusType::PENDING)
			->withEmail($this->userMayUpload1->email)
			->create();

		// Create item with album_id = null (simulating deleted album via FK set-null cascade).
		OrderItem::factory()
			->forOrder($order)
			->forPhoto($this->photo1)
			->forAlbum(null)
			->create();

		$response = $this->actingAs($this->userMayUpload1)->getJson("Shop/Order/{$order->id}");

		$this->assertOk($response);
		$items = $response->json('items');
		$this->assertCount(1, $items);
		$this->assertNull($items[0]['album_title']);
	}

	// -------------------------------------------------------------------------
	// T-042-05: thumb_url is null when photo deleted (S-042-04)
	// -------------------------------------------------------------------------

	public function testThumbUrlIsNullWhenPhotoIsAbsent(): void
	{
		$order = Order::factory()
			->forUser($this->userMayUpload1)
			->withTransactionId(Str::uuid()->toString())
			->withProvider(OmnipayProviderType::DUMMY)
			->withStatus(PaymentStatusType::PENDING)
			->withEmail($this->userMayUpload1->email)
			->create();

		// Create item with photo_id = null (simulating deleted photo via FK set-null cascade).
		OrderItem::factory()
			->forOrder($order)
			->forPhoto(null)
			->forAlbum($this->album1)
			->create();

		$response = $this->actingAs($this->userMayUpload1)->getJson("Shop/Order/{$order->id}");

		$this->assertOk($response);
		$items = $response->json('items');
		$this->assertCount(1, $items);
		$this->assertNull($items[0]['thumb_url']);
	}

	// -------------------------------------------------------------------------
	// T-042-06: thumb_url is null when photo has no THUMB size variant (S-042-05)
	// -------------------------------------------------------------------------

	public function testThumbUrlIsNullWhenPhotoHasNoThumbVariant(): void
	{
		$photo_without_thumb = Photo::factory()
			->owned_by($this->userMayUpload1)
			->create();

		// Delete the THUMB size variant so only non-thumb variants remain.
		SizeVariant::where('photo_id', $photo_without_thumb->id)
			->where('type', SizeVariantType::THUMB)
			->delete();

		$order = Order::factory()
			->forUser($this->userMayUpload1)
			->withTransactionId(Str::uuid()->toString())
			->withProvider(OmnipayProviderType::DUMMY)
			->withStatus(PaymentStatusType::PENDING)
			->withEmail($this->userMayUpload1->email)
			->create();

		OrderItem::factory()
			->forOrder($order)
			->forPhoto($photo_without_thumb)
			->forAlbum($this->album1)
			->create();

		$response = $this->actingAs($this->userMayUpload1)->getJson("Shop/Order/{$order->id}");

		$this->assertOk($response);
		$items = $response->json('items');
		$this->assertCount(1, $items);
		$this->assertNull($items[0]['thumb_url']);
	}
}
