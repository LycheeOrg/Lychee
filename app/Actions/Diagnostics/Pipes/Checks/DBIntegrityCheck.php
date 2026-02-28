<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Enum\SizeVariantType;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
		if (!Schema::hasTable('size_variants') || !Schema::hasTable('photos')) {
			return $next($data);
		}

		$sub_join = DB::table('size_variants')->where('size_variants.type', '=', SizeVariantType::ORIGINAL->value)->select('id', 'photo_id');
		$photos = Photo::query()
			->with(['albums'])
			->select(['photos.id', 'title'])
			->joinSub($sub_join, 'size_variants', 'size_variants.photo_id', '=', 'photos.id', 'left')
			->whereNull('size_variants.id')
			->get();

		foreach ($photos as $photo) {
			$data[] = DiagnosticData::error('Photo without Original found -- ' . $photo->title . ' in ' . ($photo->albums?->first()?->title ?? __('gallery.smart_album.unsorted')), self::class);
		}

		return $next($data);
	}
}