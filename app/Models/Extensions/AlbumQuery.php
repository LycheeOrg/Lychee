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
				'max_taken_at' => Photo::leftJoin('albums as a', 'album_id', '=', 'a.id')
					->select('taken_at')
					->where('albums._lft', '<=', DB::raw('a._lft'))
					->where('a._rgt', '<=', DB::raw('albums._rgt'))
					->whereNotNull('taken_at')
					->orderBy('taken_at', 'desc')
					->limit(1),
				'min_taken_at' => Photo::leftJoin('albums as a', 'album_id', '=', 'a.id')
					->select('taken_at')
					->where('albums._lft', '<=', DB::raw('a._lft'))
					->where('a._rgt', '<=', DB::raw('albums._rgt'))
					->whereNotNull('taken_at')
					->orderBy('taken_at', 'asc')
					->limit(1),
			]);
	}
}
