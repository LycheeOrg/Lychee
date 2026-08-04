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

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class OrderItemTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testContentUrlUsesDownloadLinkWhenSet(): void
	{
		$order = Order::factory()->create();
		$item = OrderItem::factory()->forOrder($order)->forPhoto()->create(['download_link' => 'https://example.com/custom-download']);

		self::assertEquals('https://example.com/custom-download', $item->content_url);
	}

	public function testContentUrlUsesSizeVariantDownloadUrlWhenNoDownloadLink(): void
	{
		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();
		$variant = SizeVariant::where('photo_id', $photo->id)->first();

		$order = Order::factory()->create();
		$item = OrderItem::factory()->forOrder($order)->forPhoto($photo)->create(['size_variant_id' => $variant->id]);

		$this->mock(ConfigManager::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getValueAsBool')->andReturn(false);
		});

		self::assertStringContainsString($variant->short_path, $item->content_url);
	}

	public function testContentUrlIsNullWithoutDownloadLinkOrSizeVariant(): void
	{
		$order = Order::factory()->create();
		$item = OrderItem::factory()->forOrder($order)->forAlbum()->create();

		self::assertNull($item->content_url);
	}
}
