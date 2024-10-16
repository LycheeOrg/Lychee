<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Contracts\Models\SizeVariantFactory;
use App\Enum\SizeVariantType;
use App\Http\Requests\Maintenance\CreateThumbsRequest;
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
	public function do(CreateThumbsRequest $request, SizeVariantFactory $sizeVariantFactory): void
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
			$sizeVariantFactory->init($photo);
			$sizeVariant = $sizeVariantFactory->createSizeVariantCond($request->kind());
			if ($sizeVariant !== null) {
				$generated++;
				Log::notice($request->kind()->value . ' (' . $sizeVariant->width . 'x' . $sizeVariant->height . ') for ' . $photo->title . ' created.');
			} else {
				Log::error('Did not create ' . $request->kind()->value . ' for ' . $photo->title . '.');
			}
		}
	}

	/**
	 * Check how many images needs to be created.
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
