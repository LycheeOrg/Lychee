<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Metrics;

use App\Exceptions\UnauthorizedException;
use App\Models\LiveMetrics;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class GetMetrics
{
	public function get(): Collection
	{
		/** @var User $user */
		$user = Auth::user() ?? throw new UnauthorizedException();

		return LiveMetrics::query()->with(['photo', 'photo.size_variants', 'album', 'album_impl', 'album.thumb'])
			->join('photos', 'photos.id', '=', 'live_metrics.photo_id', 'left')
			->join('base_albums', 'base_albums.id', '=', 'live_metrics.album_id', 'left')
			->join('albums', 'albums.id', '=', 'live_metrics.album_id', 'left')

			// Owner check (if not admin)
			->when($user->may_administrate, fn ($q_owner) => $q_owner
				->where(fn ($q) => $q
					->where('base_albums.owner_id', $user->id)
					->orWhere('photos.owner_id', $user->id))
			)

			// Do not fetch the visit for photos (too noisy)
			->where(fn ($q) => $q->where('live_metrics.action', '!=', 'visit')
				->orWhere(fn ($q1) => $q1->where('live_metrics.action', 'visit')
					->whereNotNull('live_metrics.album_id')))

			// Do not fetch the tag albums too.
			// Maybe refactor if we decide to unify tag albums and normal albums...
			->where(fn ($q) => $q->whereNull('live_metrics.album_id')
				->orWhereNotNull('albums.id'))

			->select([
				'live_metrics.*',
				// // 	// 'photos.title as photo_title',
				// // 	// 'base_albums.title as album_title',
			])
			->orderBy('live_metrics.created_at', 'desc')
			->get();
	}
}
