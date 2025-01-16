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
	public function do(CreateThumbsRequest $request, SizeVariantFactory $sizeVariantFactory, PlaceholderEncoder $placeholderEncoder): void
	{
		$photos = Photo::query()
			->where('type', 'like', 'image/%')
			->with('size_variants')
			->whereDoesntHave('size_variants', function (Builder $query) use ($request) {
				$query->where('type', '=', $request->kind());
			})
			->take(100)
			->get();

		$generated = 0;
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			// @codeCoverageIgnoreStart
			$sizeVariantFactory->init($photo);
			try {
				$sizeVariant = $sizeVariantFactory->createSizeVariantCond($request->kind());
				if ($request->kind() === SizeVariantType::PLACEHOLDER && $sizeVariant !== null) {
					$placeholderEncoder->do($sizeVariant);
				}
				if ($sizeVariant !== null) {
					$generated++;
					Log::notice($request->kind()->value . ' (' . $sizeVariant->width . 'x' . $sizeVariant->height . ') for ' . $photo->title . ' created.');
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
	public function check(CreateThumbsRequest $request, SizeVariantDimensionHelpers $svHelpers): int
	{
		if (!$svHelpers->isEnabledByConfiguration($request->kind())) {
			return 0;
		}

		$numGenerated = SizeVariant::query()->where('type', '=', $request->kind())->count();

		$totalToHave = SizeVariant::query()->where(fn ($q) => $q
				->when($svHelpers->getMaxWidth($request->kind()) !== 0, fn ($q1) => $q1->where('width', '>', $svHelpers->getMaxWidth($request->kind())))
				->when($svHelpers->getMaxHeight($request->kind()) !== 0, fn ($q2) => $q2->orWhere('height', '>', $svHelpers->getMaxHeight($request->kind())))
		)
		->where('type', '=', SizeVariantType::ORIGINAL)
		->count();

		return $totalToHave - $numGenerated;
	}
}
