<?php

namespace App\Models\Extensions;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait AlbumQuery
{
	public static function initQuery(): Builder
	{
		return self::with(['owner', 'cover'])->select('albums.*')
			->addSelect([
				'max_takestamp' => Photo::leftJoin('albums as a', 'album_id', '=', 'a.id')
					->select('takestamp')
					->where('albums._lft', '<=', DB::raw('a._lft'))
					->where('a._rgt', '<=', DB::raw('albums._rgt'))
					->whereNotNull('takestamp')
					->orderBy('takestamp', 'desc')
					->limit(1),
				'min_takestamp' => Photo::leftJoin('albums as a', 'album_id', '=', 'a.id')
					->select('takestamp')
					->where('albums._lft', '<=', DB::raw('a._lft'))
					->where('a._rgt', '<=', DB::raw('albums._rgt'))
					->whereNotNull('takestamp')
					->orderBy('takestamp', 'asc')
					->limit(1),
			]);
	}
}
