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

namespace Tests\Unit\DTO;

use App\DTO\WebhookPayload;
use Tests\AbstractTestCase;

/**
 * Unit tests for WebhookPayload DTO.
 *
 * Verifies that toJsonArray() and toQueryArray() correctly include/exclude
 * fields and encode size variant URLs.
 */
class WebhookPayloadTest extends AbstractTestCase
{
	// ── toJsonArray ───────────────────────────────────────────────────────────

	public function testToJsonArrayAllNull(): void
	{
		$payload = new WebhookPayload(null, null, null, null);

		$this->assertSame([], $payload->toJsonArray());
	}

	public function testToJsonArrayAllSet(): void
	{
		$variants = [
			['type' => 'original', 'url' => 'https://example.com/original.jpg'],
			['type' => 'medium', 'url' => 'https://example.com/medium.jpg'],
		];
		$payload = new WebhookPayload('photo-123', 'album-456', 'Sunset', $variants);

		$json = $payload->toJsonArray();

		$this->assertSame('photo-123', $json['photo_id']);
		$this->assertSame('album-456', $json['album_id']);
		$this->assertSame('Sunset', $json['title']);
		$this->assertSame($variants, $json['size_variants']);
	}

	public function testToJsonArrayOnlyPhotoId(): void
	{
		$payload = new WebhookPayload('photo-123', null, null, null);

		$json = $payload->toJsonArray();

		$this->assertArrayHasKey('photo_id', $json);
		$this->assertArrayNotHasKey('album_id', $json);
		$this->assertArrayNotHasKey('title', $json);
		$this->assertArrayNotHasKey('size_variants', $json);
	}

	public function testToJsonArrayOnlyAlbumId(): void
	{
		$payload = new WebhookPayload(null, 'album-456', null, null);

		$json = $payload->toJsonArray();

		$this->assertArrayNotHasKey('photo_id', $json);
		$this->assertArrayHasKey('album_id', $json);
		$this->assertArrayNotHasKey('title', $json);
		$this->assertArrayNotHasKey('size_variants', $json);
	}

	public function testToJsonArrayOnlyTitle(): void
	{
		$payload = new WebhookPayload(null, null, 'My Photo', null);

		$json = $payload->toJsonArray();

		$this->assertArrayNotHasKey('photo_id', $json);
		$this->assertArrayNotHasKey('album_id', $json);
		$this->assertArrayHasKey('title', $json);
		$this->assertSame('My Photo', $json['title']);
		$this->assertArrayNotHasKey('size_variants', $json);
	}

	public function testToJsonArrayOnlySizeVariants(): void
	{
		$variants = [['type' => 'thumb', 'url' => 'https://cdn.example.com/t.jpg']];
		$payload = new WebhookPayload(null, null, null, $variants);

		$json = $payload->toJsonArray();

		$this->assertArrayNotHasKey('photo_id', $json);
		$this->assertArrayNotHasKey('album_id', $json);
		$this->assertArrayNotHasKey('title', $json);
		$this->assertSame($variants, $json['size_variants']);
	}

	// ── toQueryArray ──────────────────────────────────────────────────────────

	public function testToQueryArrayAllNull(): void
	{
		$payload = new WebhookPayload(null, null, null, null);

		$this->assertSame([], $payload->toQueryArray());
	}

	public function testToQueryArrayScalarFieldsPassedAsIs(): void
	{
		$payload = new WebhookPayload('photo-abc', 'album-xyz', 'Sunrise', null);

		$query = $payload->toQueryArray();

		$this->assertSame('photo-abc', $query['photo_id']);
		$this->assertSame('album-xyz', $query['album_id']);
		$this->assertSame('Sunrise', $query['title']);
		$this->assertArrayNotHasKey('size_variants', $query);
	}

	public function testToQueryArraySizeVariantsAreBase64Encoded(): void
	{
		$originalUrl = 'https://cdn.example.com/photos/original.jpg';
		$mediumUrl = 'https://cdn.example.com/photos/medium.jpg';
		$variants = [
			['type' => 'original', 'url' => $originalUrl],
			['type' => 'medium', 'url' => $mediumUrl],
		];
		$payload = new WebhookPayload(null, null, null, $variants);

		$query = $payload->toQueryArray();

		$this->assertArrayHasKey('size_variant_original', $query);
		$this->assertArrayHasKey('size_variant_medium', $query);
		$this->assertSame(base64_encode($originalUrl), $query['size_variant_original']);
		$this->assertSame(base64_encode($mediumUrl), $query['size_variant_medium']);
	}

	public function testToQueryArrayEmptySizeVariants(): void
	{
		$payload = new WebhookPayload(null, null, null, []);

		$query = $payload->toQueryArray();

		// Empty array: no size_variant_* keys
		$this->assertEmpty(array_filter(
			array_keys($query),
			fn (string $k): bool => str_starts_with($k, 'size_variant_'),
		));
	}

	public function testToQueryArrayMixedFields(): void
	{
		$url = 'https://s3.amazonaws.com/bucket/thumb.jpg?token=abc&expires=123';
		$payload = new WebhookPayload('pid', null, null, [['type' => 'thumb', 'url' => $url]]);

		$query = $payload->toQueryArray();

		$this->assertSame('pid', $query['photo_id']);
		$this->assertArrayNotHasKey('album_id', $query);
		$this->assertSame(base64_encode($url), $query['size_variant_thumb']);
	}
}
