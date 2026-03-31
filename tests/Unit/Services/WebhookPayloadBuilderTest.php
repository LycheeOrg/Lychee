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

namespace Tests\Unit\Services;

use App\Enum\SizeVariantType;
use App\Models\Webhook;
use App\Services\Webhook\WebhookPayloadBuilder;
use Tests\AbstractTestCase;

/**
 * Unit tests for WebhookPayloadBuilder.
 *
 * Uses a Webhook model stub (no database) to verify the build() logic and
 * the size-variant filtering.
 */
class WebhookPayloadBuilderTest extends AbstractTestCase
{
	private WebhookPayloadBuilder $builder;

	/** @var array<int,array{type:string,url:string}> */
	private array $allVariants;

	public function setUp(): void
	{
		parent::setUp();

		$this->builder = new WebhookPayloadBuilder();

		$this->allVariants = [
			['type' => SizeVariantType::ORIGINAL->name(), 'url' => 'https://example.com/original.jpg'],
			['type' => SizeVariantType::MEDIUM->name(), 'url' => 'https://example.com/medium.jpg'],
			['type' => SizeVariantType::THUMB->name(), 'url' => 'https://example.com/thumb.jpg'],
		];
	}

	// ── helpers ───────────────────────────────────────────────────────────────

	/**
	 * Build a partially-hydrated Webhook stub without hitting the database.
	 *
	 * @param array<string,mixed> $attributes
	 *
	 * @return Webhook
	 */
	private function makeWebhook(array $attributes): Webhook
	{
		$webhook = new Webhook();
		foreach ($attributes as $key => $value) {
			$webhook->{$key} = $value;
		}

		return $webhook;
	}

	// ── send_* flag tests ─────────────────────────────────────────────────────

	public function testBuildIncludesAllFieldsWhenAllFlagsTrue(): void
	{
		$webhook = $this->makeWebhook([
			'send_photo_id' => true,
			'send_album_id' => true,
			'send_title' => true,
			'send_size_variants' => true,
			'size_variant_types' => null,
		]);

		$payload = $this->builder->build($webhook, 'pid', 'aid', 'My Photo', $this->allVariants);

		$this->assertSame('pid', $payload->photo_id);
		$this->assertSame('aid', $payload->album_id);
		$this->assertSame('My Photo', $payload->title);
		$this->assertCount(3, $payload->size_variants);
	}

	public function testBuildExcludesAllFieldsWhenAllFlagsFalse(): void
	{
		$webhook = $this->makeWebhook([
			'send_photo_id' => false,
			'send_album_id' => false,
			'send_title' => false,
			'send_size_variants' => false,
			'size_variant_types' => null,
		]);

		$payload = $this->builder->build($webhook, 'pid', 'aid', 'My Photo', $this->allVariants);

		$this->assertNull($payload->photo_id);
		$this->assertNull($payload->album_id);
		$this->assertNull($payload->title);
		$this->assertNull($payload->size_variants);
	}

	public function testBuildIncludesOnlyPhotoId(): void
	{
		$webhook = $this->makeWebhook([
			'send_photo_id' => true,
			'send_album_id' => false,
			'send_title' => false,
			'send_size_variants' => false,
			'size_variant_types' => null,
		]);

		$payload = $this->builder->build($webhook, 'pid', 'aid', 'My Photo', $this->allVariants);

		$this->assertSame('pid', $payload->photo_id);
		$this->assertNull($payload->album_id);
		$this->assertNull($payload->title);
		$this->assertNull($payload->size_variants);
	}

	public function testBuildIncludesOnlyAlbumId(): void
	{
		$webhook = $this->makeWebhook([
			'send_photo_id' => false,
			'send_album_id' => true,
			'send_title' => false,
			'send_size_variants' => false,
			'size_variant_types' => null,
		]);

		$payload = $this->builder->build($webhook, 'pid', 'aid', 'My Photo', $this->allVariants);

		$this->assertNull($payload->photo_id);
		$this->assertSame('aid', $payload->album_id);
	}

	// ── size variant filtering ────────────────────────────────────────────────

	public function testFilterSizeVariantsWithNullTypesIncludesAll(): void
	{
		$webhook = $this->makeWebhook([
			'send_photo_id' => false,
			'send_album_id' => false,
			'send_title' => false,
			'send_size_variants' => true,
			'size_variant_types' => null,
		]);

		$payload = $this->builder->build($webhook, 'pid', 'aid', 'T', $this->allVariants);

		$this->assertCount(3, $payload->size_variants);
	}

	public function testFilterSizeVariantsWithEmptyArrayIncludesAll(): void
	{
		$webhook = $this->makeWebhook([
			'send_photo_id' => false,
			'send_album_id' => false,
			'send_title' => false,
			'send_size_variants' => true,
			'size_variant_types' => [],
		]);

		$payload = $this->builder->build($webhook, 'pid', 'aid', 'T', $this->allVariants);

		$this->assertCount(3, $payload->size_variants);
	}

	public function testFilterSizeVariantsWithSpecificTypes(): void
	{
		// Only include ORIGINAL (int 1) and THUMB (int 7)
		$webhook = $this->makeWebhook([
			'send_photo_id' => false,
			'send_album_id' => false,
			'send_title' => false,
			'send_size_variants' => true,
			'size_variant_types' => [SizeVariantType::ORIGINAL->value, SizeVariantType::THUMB->value],
		]);

		$payload = $this->builder->build($webhook, 'pid', 'aid', 'T', $this->allVariants);

		$this->assertCount(2, $payload->size_variants);
		$types = array_column($payload->size_variants, 'type');
		$this->assertContains('original', $types);
		$this->assertContains('thumb', $types);
		$this->assertNotContains('medium', $types);
	}

	public function testFilterSizeVariantsWithTypeNotInSnapshot(): void
	{
		// Request MEDIUM2X which is not in the snapshot — should be silently omitted.
		$webhook = $this->makeWebhook([
			'send_photo_id' => false,
			'send_album_id' => false,
			'send_title' => false,
			'send_size_variants' => true,
			'size_variant_types' => [SizeVariantType::MEDIUM2X->value],
		]);

		$payload = $this->builder->build($webhook, 'pid', 'aid', 'T', $this->allVariants);

		$this->assertIsArray($payload->size_variants);
		$this->assertCount(0, $payload->size_variants);
	}

	public function testFilterSizeVariantsWithEmptySnapshotAndNullTypes(): void
	{
		$webhook = $this->makeWebhook([
			'send_photo_id' => false,
			'send_album_id' => false,
			'send_title' => false,
			'send_size_variants' => true,
			'size_variant_types' => null,
		]);

		$payload = $this->builder->build($webhook, 'pid', 'aid', 'T', []);

		$this->assertIsArray($payload->size_variants);
		$this->assertCount(0, $payload->size_variants);
	}
}
