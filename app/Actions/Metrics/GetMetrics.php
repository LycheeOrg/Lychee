<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Metrics;

use App\Exceptions\UnauthorizedException;
use App\Models\LiveMetrics;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class GetMetrics
{
	public function get(): Collection
	{
		$id = Auth::id() ?? throw new UnauthorizedException();

		return LiveMetrics::query()
			->join('photos', 'photos.id', '=', 'live_metrics.photo_id', 'left')
			->join('base_albums', 'base_albums.id', '=', 'live_metrics.album_id', 'left')
			->where(fn ($q) => $q->where('base_albums.owner_id', $id)
				->orWhere('photos.owner_id', $id))

			->where(fn ($q) => $q->where('live_metrics.action', '!=', 'visit')
				->orWhere(fn ($q1) => $q1->where('live_metrics.action', 'visit')
					->whereNotNull('live_metrics.album_id')))

			->select([
				'live_metrics.*',
				'photos.title as photo_title',
				'base_albums.title as album_title',
			])
			->orderBy('live_metrics.created_at', 'desc')
			->get();
	}
}
