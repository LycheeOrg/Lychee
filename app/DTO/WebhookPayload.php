<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * Data transfer object representing the payload to be sent to a webhook endpoint.
 *
 * Fields are nullable; a null value means the field was not selected for inclusion.
 */
final class WebhookPayload
{
	/**
	 * @param string|null                             $photo_id      The photo ID (null if not selected).
	 * @param string|null                             $album_id      The album ID (null if not selected).
	 * @param string|null                             $title         The photo title (null if not selected).
	 * @param array<int,array{type:string,url:string}>|null $size_variants Array of {type, url} objects (null if not selected).
	 */
	public function __construct(
		public readonly ?string $photo_id,
		public readonly ?string $album_id,
		public readonly ?string $title,
		public readonly ?array $size_variants,
	) {
	}

	/**
	 * Return the payload as a flat associative array for JSON encoding.
	 * Only non-null fields are included.
	 *
	 * @return array<string, mixed>
	 */
	public function toJsonArray(): array
	{
		$payload = [];

		if ($this->photo_id !== null) {
			$payload['photo_id'] = $this->photo_id;
		}
		if ($this->album_id !== null) {
			$payload['album_id'] = $this->album_id;
		}
		if ($this->title !== null) {
			$payload['title'] = $this->title;
		}
		if ($this->size_variants !== null) {
			$payload['size_variants'] = $this->size_variants;
		}

		return $payload;
	}

	/**
	 * Return the payload as a flat associative array for query-string encoding.
	 *
	 * Scalar fields (photo_id, album_id, title) are passed as-is.
	 * Size variant URLs are base64-encoded (standard base64) and keyed as
	 * `size_variant_{type}` — e.g. `size_variant_original`, `size_variant_medium`.
	 * This avoids URL-encoding ambiguity for S3/CDN URLs.
	 *
	 * @return array<string, string>
	 */
	public function toQueryArray(): array
	{
		$payload = [];

		if ($this->photo_id !== null) {
			$payload['photo_id'] = $this->photo_id;
		}
		if ($this->album_id !== null) {
			$payload['album_id'] = $this->album_id;
		}
		if ($this->title !== null) {
			$payload['title'] = $this->title;
		}
		if ($this->size_variants !== null) {
			foreach ($this->size_variants as $variant) {
				$key = 'size_variant_' . $variant['type'];
				$payload[$key] = base64_encode($variant['url']);
			}
		}

		return $payload;
	}
}
