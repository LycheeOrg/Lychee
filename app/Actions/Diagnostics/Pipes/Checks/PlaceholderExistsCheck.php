<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
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
	public const NUM_PLACEHOLDER = 'num_placeholder';
	public const MAX_NUM_PLACEHOLDER = 'max_num_placeholder';
	public const NUM_UNENCODED_PLACEHOLDER = 'num_unencoded_placeholder';
	public const INFO_MSG_MISSING = 'Info: Found %d placeholders that could be generated.';
	public const INFO_LINE_MISSING = '     You can use `php artisan lychee:generate_thumbs placeholder %d` to generate them.';
	public const INFO_MSG_UNENCODED = 'Info: Found %d placeholder images that have not been encoded.';
	public const INFO_LINE_UNENCODED = '     You can use `php artisan lychee:encode_placeholders %d` to encode them.';

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

		$result = DB::query()
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where('type', '=', SizeVariantType::PLACEHOLDER),
			self::NUM_PLACEHOLDER
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where('type', '=', SizeVariantType::ORIGINAL),
			self::MAX_NUM_PLACEHOLDER
		)
		->selectSub(
			SizeVariant::query()
			->selectRaw('COUNT(*)')
			->where('short_path', 'LIKE', '%placeholder/%'),
			self::NUM_UNENCODED_PLACEHOLDER
		)
		->first();

		$num = $result->{self::NUM_UNENCODED_PLACEHOLDER};
		if ($num > 0) {
			$data[] = sprintf(self::INFO_MSG_UNENCODED, $num);
			$data[] = sprintf(self::INFO_LINE_UNENCODED, $num);
		}

		$num = $result->{self::MAX_NUM_PLACEHOLDER} - $result->{self::NUM_PLACEHOLDER};
		if ($num > 0) {
			$data[] = sprintf(self::INFO_MSG_MISSING, $num);
			$data[] = sprintf(self::INFO_LINE_MISSING, $num);
		}

		return $next($data);
	}
}