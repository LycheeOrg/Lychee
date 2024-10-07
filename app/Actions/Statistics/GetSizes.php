<?php

namespace App\Actions\Statistics;

use App\Enum\SizeVariantType;
use App\Facades\Helpers;
use Illuminate\Support\Facades\DB;

class GetSizes
{
	/**
	 * Return the amount of data stored on the server.
	 *
	 * @return array{type: SizeVariantType, size: string, formatted: string}[]
	 */
	public function getFullSize(): array
	{
		return DB::table('size_variants')
		->select(
			'type',
			DB::raw('SUM(filesize) as size')
		)
		->groupBy('type')
		->get()
		->map(function ($item) {
			return [
				'type' => SizeVariantType::from($item->type),
				'size' => $item->size,
				'formatted' => Helpers::getSymbolByQuantity((float) $item->size),
			];
		})->all();
	}
}