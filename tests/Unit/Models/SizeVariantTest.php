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

use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Image\Files\FlysystemFile;
use App\Models\Configs;
use App\Models\SizeVariant;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

class SizeVariantTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function mockConfigManagerToAvoidSignedUrls(): void
	{
		$this->mock(ConfigManager::class, function (MockInterface $mock): void {
			$mock->shouldReceive('getValueAsBool')->andReturn(false);
		});
	}

	public function testIsWatermarkedIsFalseWhenPathAbsent(): void
	{
		$variant = new SizeVariant();
		self::assertFalse($variant->is_watermarked);

		$variant->short_path_watermarked = '';
		self::assertFalse($variant->is_watermarked);
	}

	public function testIsWatermarkedIsTrueWhenPathPresent(): void
	{
		$variant = new SizeVariant();
		$variant->short_path_watermarked = 'watermarked/foo.jpg';
		self::assertTrue($variant->is_watermarked);
	}

	public function testGetFileReturnsFlysystemFileForConfiguredDisk(): void
	{
		$variant = new SizeVariant();
		$variant->storage_disk = StorageDiskType::LOCAL;
		$variant->short_path = 'small/foo.jpg';

		self::assertInstanceOf(FlysystemFile::class, $variant->getFile());
	}

	public function testGetDownloadUrlAttributeContainsShortPath(): void
	{
		$this->mockConfigManagerToAvoidSignedUrls();

		$variant = new SizeVariant();
		$variant->storage_disk = StorageDiskType::LOCAL;
		$variant->short_path = 'small/foo.jpg';
		$variant->type = SizeVariantType::SMALL;

		self::assertStringContainsString('small/foo.jpg', $variant->getDownloadUrlAttribute());
	}

	public function testGetUrlAttributeReturnsDataUriForPlaceholder(): void
	{
		$variant = new SizeVariant();
		$variant->type = SizeVariantType::PLACEHOLDER;
		$variant->short_path = 'base64content==';

		self::assertEquals('data:image/webp;base64,base64content==', $variant->getUrlAttribute());
	}

	public function testGetUrlAttributeReturnsRegularUrlWhenNotWatermarked(): void
	{
		Configs::set('watermark_enabled', '0');
		$this->mockConfigManagerToAvoidSignedUrls();

		$variant = new SizeVariant();
		$variant->storage_disk = StorageDiskType::LOCAL;
		$variant->short_path = 'small/foo.jpg';
		$variant->type = SizeVariantType::SMALL;

		self::assertStringContainsString('small/foo.jpg', $variant->getUrlAttribute());
	}
}
