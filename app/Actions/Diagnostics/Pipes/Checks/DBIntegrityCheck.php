<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;

/**
 * This checks the Database integrity.
 * More precisely if there are any pictures without an original.
 *
 * Such cases will crash the front-end.
 */
class DBIntegrityCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$subJoin = DB::table('size_variants')->where('size_variants.type', '=', 0);
		$photos = Photo::query()
			->with(['album'])
			->select(['photos.id', 'title', 'album_id'])
			->joinSub($subJoin, 'size_variants', 'size_variants.photo_id', '=', 'photos.id', 'left')
			->whereNull('size_variants.id')
			->get();

		foreach ($photos as $photo) {
			$data[] = 'Error: Photo without Original found -- ' . $photo->title . ' in ' . ($photo->album?->title ?? __('lychee.UNSORTED'));
		}

		return $next($data);
	}
}