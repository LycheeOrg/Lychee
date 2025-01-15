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
 * Check if there are placeholders that can be generated or encoded.
 */
class PlaceholderExistsCheck implements DiagnosticPipe
{
	public const INFO_MSG_MISSING = 'Found %d placeholders that could be generated.';
	public const INFO_LINE_MISSING = 'You can use `php artisan lychee:generate_thumbs placeholder %d` to generate them.';
	public const INFO_MSG_UNENCODED = 'Found %d placeholder images that have not been encoded.';
	public const INFO_LINE_UNENCODED = 'You can use `php artisan lychee:encode_placeholders %d` to encode them.';

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
		if (!$svHelpers->isEnabledByConfiguration(SizeVariantType::PLACEHOLDER)) {
			return $next($data);
		}

		/** @var object{num_placeholder:int,max_num_placeholder:int,num_unencoded_placeholder:int}> $result */
		$result = DB::query()
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where('type', '=', SizeVariantType::PLACEHOLDER),
			'num_placeholder'
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where('type', '=', SizeVariantType::ORIGINAL),
			'max_num_placeholder'
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where('short_path', 'LIKE', '%placeholder/%'),
			'num_unencoded_placeholder'
		)
		->first();

		$num = $result->num_unencoded_placeholder;
		if ($num > 0) {
			$data[] = DiagnosticData::info(sprintf(self::INFO_MSG_UNENCODED, $num), self::class, [sprintf(self::INFO_LINE_UNENCODED, $num)]);
		}

		$num = $result->max_num_placeholder - $result->num_placeholder;
		if ($num > 0) {
			$data[] = DiagnosticData::info(sprintf(self::INFO_MSG_MISSING, $num), self::class, [sprintf(self::INFO_LINE_MISSING, $num)]);
		}

		return $next($data);
	}
}