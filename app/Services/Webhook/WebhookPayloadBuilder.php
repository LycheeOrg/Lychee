<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services\Webhook;

use App\DTO\WebhookPayload;
use App\Enum\SizeVariantType;
use App\Models\Webhook;

/**
 * Builds a WebhookPayload DTO from a photo snapshot and a Webhook configuration.
 *
 * Applies the `send_*` flags and filters size_variants by the selected types.
 */
class WebhookPayloadBuilder
{
	/**
	 * Build a WebhookPayload for the given webhook and photo snapshot data.
	 *
	 * @param Webhook                                          $webhook       The webhook configuration.
	 * @param string                                           $photo_id      The photo ID.
	 * @param string                                           $album_id      The album ID.
	 * @param string                                           $title         The photo title.
	 * @param array<int,array{type:string,url:string}>         $size_variants All available size variants for the photo (type = lowercase name, url = full URL).
	 *
	 * @return WebhookPayload
	 */
	public function build(
		Webhook $webhook,
		string $photo_id,
		string $album_id,
		string $title,
		array $size_variants,
	): WebhookPayload {
		return new WebhookPayload(
			photo_id: $webhook->send_photo_id ? $photo_id : null,
			album_id: $webhook->send_album_id ? $album_id : null,
			title: $webhook->send_title ? $title : null,
			size_variants: $webhook->send_size_variants ? $this->filterSizeVariants($webhook, $size_variants) : null,
		);
	}

	/**
	 * Filter size variants according to the webhook's size_variant_types selection.
	 *
	 * If size_variant_types is null or empty, all provided variants are included.
	 * Variants not present in $size_variants are silently omitted.
	 *
	 * @param Webhook                                    $webhook       The webhook configuration.
	 * @param array<int,array{type:string,url:string}>   $size_variants All available size variants.
	 *
	 * @return array<int,array{type:string,url:string}>
	 */
	private function filterSizeVariants(Webhook $webhook, array $size_variants): array
	{
		$selected_types = $webhook->size_variant_types;

		if ($selected_types === null || count($selected_types) === 0) {
			return $size_variants;
		}

		// Convert the stored int values to the lowercase SizeVariantType names for comparison.
		$selected_names = array_map(
			fn (int $int_value): string => SizeVariantType::from($int_value)->name(),
			$selected_types,
		);

		return array_values(
			array_filter(
				$size_variants,
				fn (array $variant): bool => in_array($variant['type'], $selected_names, true),
			),
		);
	}
}
