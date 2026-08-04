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

namespace Tests\Unit\Models;

use App\Enum\PurchasableLicenseType;
use App\Enum\PurchasableSizeVariantType;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Purchasable;
use App\Models\User;
use App\Services\MoneyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class PurchasableTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function makeAlbum(): Album
	{
		$user = User::factory()->may_administrate()->create();

		return Album::factory()->as_root()->owned_by($user)->create();
	}

	public function testGetPriceForReturnsNullWhenNoMatchingPrice(): void
	{
		$purchasable = Purchasable::factory()->forAlbum($this->makeAlbum()->id)->create();

		self::assertNull($purchasable->getPriceFor(PurchasableSizeVariantType::MEDIUM, PurchasableLicenseType::PERSONAL));
	}

	public function testSetPriceForCreatesThenUpdatesPrice(): void
	{
		$purchasable = Purchasable::factory()->forAlbum($this->makeAlbum()->id)->create();
		$money_service = resolve(MoneyService::class);

		$purchasable->setPriceFor(
			PurchasableSizeVariantType::MEDIUM,
			PurchasableLicenseType::PERSONAL,
			$money_service->createFromCents(1000)
		);

		$price = $purchasable->getPriceFor(PurchasableSizeVariantType::MEDIUM, PurchasableLicenseType::PERSONAL);
		self::assertNotNull($price);
		self::assertEquals(1000, $price->getAmount());

		// Setting again for the same size/license combination updates rather than duplicates.
		$purchasable->setPriceFor(
			PurchasableSizeVariantType::MEDIUM,
			PurchasableLicenseType::PERSONAL,
			$money_service->createFromCents(2000)
		);

		self::assertEquals(1, $purchasable->prices()->count());
		$updated_price = $purchasable->getPriceFor(PurchasableSizeVariantType::MEDIUM, PurchasableLicenseType::PERSONAL);
		self::assertEquals(2000, $updated_price->getAmount());
	}

	public function testIsAlbumLevelTrueOnlyWhenPhotoIdIsNull(): void
	{
		$user = User::factory()->may_administrate()->create();
		$album = $this->makeAlbum();

		$album_level = Purchasable::factory()->forAlbum($album->id)->create();
		self::assertTrue($album_level->isAlbumLevel());

		$photo = Photo::factory()->owned_by($user)->create();
		$photo_level = Purchasable::factory()->forPhoto($photo->id, $album->id)->create();
		self::assertFalse($photo_level->isAlbumLevel());
	}
}
