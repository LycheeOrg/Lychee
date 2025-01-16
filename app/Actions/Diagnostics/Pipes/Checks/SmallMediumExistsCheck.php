<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Enum\SizeVariantType;
use App\Image\SizeVariantDimensionHelpers;
use App\Models\SizeVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Check if there are some small or medium that could be generated.
 */
class SmallMediumExistsCheck implements DiagnosticPipe
{
	public const NUM_SMALL = 'num_small';
	public const NUM_MEDIUM = 'num_medium';
	public const NUM_SMALL2X = 'num_small2x';
	public const NUM_MEDIUM2X = 'num_medium2x';
	public const MAX_NUM_SMALL = 'max_num_small';
	public const MAX_NUM_MEDIUM = 'max_num_medium';
	public const MAX_NUM_SMALL2X = 'max_num_small2x';
	public const MAX_NUM_MEDIUM2X = 'max_num_medium2x';
	public const INFO_MSG = 'Found %d %s that could be generated.';
	public const INFO_LINE = 'You can use `php artisan lychee:generate_thumbs %s %d` to generate them.';

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if (!Schema::hasTable('configs') || !Schema::hasTable('size_variants')) {
			// @codeCoverageIgnoreStart
			return $next($data);
			// @codeCoverageIgnoreEnd
		}

		$svHelpers = new SizeVariantDimensionHelpers();

		/** @var object{num_small:int,num_medium:int,num_small2x:int,num_medium2x:int,max_num_small:int,max_num_medium:int,max_num_small2x:int,max_num_medium2x:int} $result */
		$result = DB::query()
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where('type', '=', SizeVariantType::SMALL),
			self::NUM_SMALL
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where('type', '=', SizeVariantType::SMALL2X),
			self::NUM_SMALL2X
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where('type', '=', SizeVariantType::MEDIUM),
			self::NUM_MEDIUM
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where('type', '=', SizeVariantType::MEDIUM2X),
			self::NUM_MEDIUM2X
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where(fn ($q) => $q
				->when($svHelpers->getMaxWidth(SizeVariantType::SMALL) !== 0, fn ($q1) => $q1->where('width', '>', $svHelpers->getMaxWidth(SizeVariantType::SMALL)))
				->when($svHelpers->getMaxHeight(SizeVariantType::SMALL) !== 0, fn ($q2) => $q2->orWhere('height', '>', $svHelpers->getMaxHeight(SizeVariantType::SMALL)))
			)
			->where('type', '=', SizeVariantType::ORIGINAL),
			self::MAX_NUM_SMALL
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where(fn ($q) => $q
				->when($svHelpers->getMaxWidth(SizeVariantType::SMALL2X) !== 0, fn ($q1) => $q1->where('width', '>', $svHelpers->getMaxWidth(SizeVariantType::SMALL2X)))
				->when($svHelpers->getMaxHeight(SizeVariantType::SMALL2X) !== 0, fn ($q2) => $q2->orWhere('height', '>', $svHelpers->getMaxHeight(SizeVariantType::SMALL2X)))
			)
			->where('type', '=', SizeVariantType::ORIGINAL),
			self::MAX_NUM_SMALL2X
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where(fn ($q) => $q
				->when($svHelpers->getMaxWidth(SizeVariantType::MEDIUM) !== 0, fn ($q1) => $q1->where('width', '>', $svHelpers->getMaxWidth(SizeVariantType::MEDIUM)))
				->when($svHelpers->getMaxHeight(SizeVariantType::MEDIUM) !== 0, fn ($q2) => $q2->orWhere('height', '>', $svHelpers->getMaxHeight(SizeVariantType::MEDIUM)))
			)
			->where('type', '=', SizeVariantType::ORIGINAL),
			self::MAX_NUM_MEDIUM
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where(fn ($q) => $q
				->when($svHelpers->getMaxWidth(SizeVariantType::MEDIUM2X) !== 0, fn ($q1) => $q1->where('width', '>', $svHelpers->getMaxWidth(SizeVariantType::MEDIUM2X)))
				->when($svHelpers->getMaxHeight(SizeVariantType::MEDIUM2X) !== 0, fn ($q2) => $q2->orWhere('height', '>', $svHelpers->getMaxHeight(SizeVariantType::MEDIUM2X)))
			)
			->where('type', '=', SizeVariantType::ORIGINAL),
			self::MAX_NUM_MEDIUM2X
		)
		->first();

		$num = $result->{self::MAX_NUM_SMALL} - $result->{self::NUM_SMALL}; // @phpstan-ignore-line
		if ($num > 0) {
			$data[] = DiagnosticData::info(
				sprintf(self::INFO_MSG, $num, SizeVariantType::SMALL->name()),
				self::class,
				[sprintf(self::INFO_LINE, SizeVariantType::SMALL->name(), $num)]
			);
		}

		$num = $result->{self::MAX_NUM_SMALL2X} - $result->{self::NUM_SMALL2X}; // @phpstan-ignore-line
		if ($num > 0 && $svHelpers->isEnabledByConfiguration(SizeVariantType::SMALL2X)) {
			$data[] = DiagnosticData::info(
				sprintf(self::INFO_MSG, $num, SizeVariantType::SMALL2X->name()),
				self::class,
				[sprintf(self::INFO_LINE, SizeVariantType::SMALL2X->name(), $num)]
			);
		}

		$num = $result->{self::MAX_NUM_MEDIUM} - $result->{self::NUM_MEDIUM}; // @phpstan-ignore-line
		if ($num > 0) {
			$data[] = DiagnosticData::info(
				sprintf(self::INFO_MSG, $num, SizeVariantType::MEDIUM->name()),
				self::class,
				[sprintf(self::INFO_LINE, SizeVariantType::MEDIUM->name(), $num)]
			);
		}

		$num = $result->{self::MAX_NUM_MEDIUM2X} - $result->{self::NUM_MEDIUM2X}; // @phpstan-ignore-line
		if ($num > 0 && $svHelpers->isEnabledByConfiguration(SizeVariantType::MEDIUM2X)) {
			$data[] = DiagnosticData::info(
				sprintf(self::INFO_MSG, $num, SizeVariantType::MEDIUM2X->name()),
				self::class,
				[sprintf(self::INFO_LINE, SizeVariantType::MEDIUM2X->name(), $num)]
			);
		}

		return $next($data);
	}
}
