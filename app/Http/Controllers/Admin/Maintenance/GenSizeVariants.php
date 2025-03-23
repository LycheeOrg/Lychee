<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin\Maintenance;

use App\Contracts\Models\SizeVariantFactory;
use App\Enum\SizeVariantType;
use App\Events\AlbumRouteCacheUpdated;
use App\Exceptions\MediaFileOperationException;
use App\Http\Requests\Maintenance\CreateThumbsRequest;
use App\Image\PlaceholderEncoder;
use App\Image\SizeVariantDimensionHelpers;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * We may miss some size variants because of generation problem,
 * transfer of files, or other.
 * This module aims to solve this issue.
 */
class GenSizeVariants extends Controller
{
	/**
	 * Generates missing size variants by chunk of 100.
	 *
	 * @return void
	 */
	public function do(CreateThumbsRequest $request, SizeVariantFactory $size_variant_factory, PlaceholderEncoder $placeholder_encoder): void
	{
		$photos_query = Photo::query()
			->where('type', 'like', 'image/%')
			->with('size_variants')
			->whereDoesntHave('size_variants', function (Builder $query) use ($request): void {
				$query->where('type', '=', $request->kind());
			});
		$photos = $photos_query->lazyById(Configs::getValueAsInt('maintenance_processing_limit'));

		$generated = 0;
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			// @codeCoverageIgnoreStart
			$size_variant_factory->init($photo);
			try {
				$size_variant = $size_variant_factory->createSizeVariantCond($request->kind());
				if ($request->kind() === SizeVariantType::PLACEHOLDER && $size_variant !== null) {
					$placeholder_encoder->do($size_variant);
				}
				if ($size_variant !== null) {
					$generated++;
					Log::notice($request->kind()->value . ' (' . $size_variant->width . 'x' . $size_variant->height . ') for ' . $photo->title . ' created.');
				} else {
					Log::error('Did not create ' . $request->kind()->value . ' for ' . $photo->title . '.');
				}
			} catch (MediaFileOperationException $e) {
				Log::error('Failed to create ' . $request->kind()->value . ' for photo id ' . $photo->id . '');
			}

			AlbumRouteCacheUpdated::dispatch();
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Check how many images need to be created.
	 *
	 * @return int
	 */
	public function check(CreateThumbsRequest $request, SizeVariantDimensionHelpers $sv_helpers): int
	{
		if (!$sv_helpers->isEnabledByConfiguration($request->kind())) {
			return 0;
		}

		$num_generated = SizeVariant::query()->where('type', '=', $request->kind())->count();

		$total_to_have = SizeVariant::query()->where(fn ($q) => $q
				->when($sv_helpers->getMaxWidth($request->kind()) !== 0, fn ($q1) => $q1->where('width', '>', $sv_helpers->getMaxWidth($request->kind())))
				->when($sv_helpers->getMaxHeight($request->kind()) !== 0, fn ($q2) => $q2->orWhere('height', '>', $sv_helpers->getMaxHeight($request->kind())))
		)
		->where('type', '=', SizeVariantType::ORIGINAL)
		->count();

		return $total_to_have - $num_generated;
	}
}
