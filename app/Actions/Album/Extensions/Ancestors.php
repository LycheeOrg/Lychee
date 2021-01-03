<?php

namespace App\Actions\Album\Extensions;

use App\Models\Album;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

trait Ancestors
{
	/**
	 * This function expect a BaseCollection of arrays containing [_lft, _rgt, min, max].
	 */
	public function getAncestorsOutdated(BaseCollection $ancestors, $min_sign = '>=', $max_sign = '<=')
	{
		$sql = Album::select('id')
			->where(DB::raw('0'));
		// initialize query with nothing!

		$ancestors->eachSpread(function ($_lft, $_rgt, $min, $max) use ($sql, $min_sign, $max_sign) {
			$sql->orWhere(fn ($q) => $q
				// this smartly select all the ancestors
				->where('_lft', '<=', $_lft)
				->where('_rgt', '>=', $_rgt)
				// this smartly select only the one that need to be updated:
				// min is bigger OR EQUAL than the min of the deleted album
				// max is small OR EQUAL than the max of the deleted album
				//? the EQUAL is the condition we are interested in, bigger/smaller is just for safety
				->where(fn ($q) => $q
					->where('min_takestamp', $min_sign, $min)
					->orWhere('max_takestamp', $max_sign, $max)));
		});

		return $sql->get();
	}
}
