<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware\Caching;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Events\AlbumRouteCacheUpdated;
use App\Models\Configs;
use Illuminate\Foundation\Http\Middleware\Concerns\ExcludesPaths;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response caching, this allows to speed up the reponse time of Lychee by hopefully a lot.
 */
class AlbumRouteCacheRefresher
{
	use ExcludesPaths;

	/** @var string[] */
	protected array $except = [
		'api/v2/Album',
		'api/v2/Album::unlock',
		'api/v2/Album::rename',
		'api/v2/Album::updateProtectionPolicy',
		'api/v2/Album::move',
		'api/v2/Album::cover',
		'api/v2/Album::header',
		'api/v2/Album::merge',
		'api/v2/Album::transfer',
		'api/v2/Album::track',
		'api/v2/TagAlbum',
		'api/v2/Sharing',
		'api/v2/Photo::fromUrl',
		'api/v2/Photo',
		'api/v2/Photo::rename',
		'api/v2/Photo::tags',
		'api/v2/Photo::move',
		'api/v2/Photo::copy',
		'api/v2/Photo::star',
		'api/v2/Photo::rotate',
	];

	/**
	 * Handle an incoming request.
	 *
	 * @param Request                                                                                           $request
	 * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
	 *
	 * @return Response
	 *
	 * @throws \InvalidArgumentException
	 */
	public function handle(Request $request, \Closure $next): mixed
	{
		if ($request->method() === 'GET') {
			return $next($request);
		}

		if (Configs::getValueAsBool('cache_enabled') === false) {
			return $next($request);
		}

		// ! We use $except as a ALLOW list instead of a DENY list
		if (!$this->inExceptArray($request)) {
			return $next($request);
		}

		$full_album_ids = collect();

		/** @var string|null $album_id */
		$album_id = $request->input(RequestAttribute::ALBUM_ID_ATTRIBUTE);
		if ($album_id !== null) {
			$full_album_ids->push($album_id);
		}

		/** @var string[]|null */
		$albums_id = $request->input(RequestAttribute::ALBUM_IDS_ATTRIBUTE);
		if ($albums_id !== null) {
			$full_album_ids = $full_album_ids->merge($albums_id);
		}

		/** @var string|null */
		$parent_id = $request->input(RequestAttribute::PARENT_ID_ATTRIBUTE);
		if ($parent_id !== null) {
			$full_album_ids->push($parent_id);
		}

		/** @var string|null */
		$photo_id = $request->input(RequestAttribute::PHOTO_ID_ATTRIBUTE);
		/** @var string[]|null */
		$photo_ids = $request->input(RequestAttribute::PHOTO_IDS_ATTRIBUTE);

		if ($photo_ids !== null || $photo_id !== null) {
			$photos_album_ids = DB::table('photos')
				->select('album_id')
				->whereIn('id', $photo_ids ?? [])
				->orWhere('id', '=', $photo_id)
				->distinct()
				->pluck('album_id')
				->all();
			if (count($photos_album_ids) > 0) {
				$full_album_ids = $full_album_ids->merge($photos_album_ids);
			}
		}

		if ($albums_id !== null || $album_id !== null) {
			$albums_parents_ids = DB::table('albums')
				->select('parent_id')
				->whereIn('id', $albums_id ?? [])
				->orWhere('id', '=', $album_id)
				->distinct()
				->pluck('parent_id')
				->all();
			if (count($albums_parents_ids) > 0) {
				$full_album_ids = $full_album_ids->merge($albums_parents_ids);
			}
		}

		$full_album_ids->each(fn ($album_id) => AlbumRouteCacheUpdated::dispatch($album_id ?? ''));

		return $next($request);
	}
}
