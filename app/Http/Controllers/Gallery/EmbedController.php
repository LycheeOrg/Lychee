<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\Embed\EmbededRequest;
use App\Http\Resources\Embed\EmbedAlbumResource;
use App\Http\Resources\Embed\EmbedStreamResource;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller responsible for providing embed data for external websites.
 */
class EmbedController extends Controller
{
	public function __construct(
		protected PhotoQueryPolicy $photo_query_policy)
	{
	}

	/**
	 * Get album data for embedding on external sites.
	 *
	 * Only public albums that don't require authentication can be embedded.
	 *
	 * Supports optional pagination via query parameters:
	 * - limit: Maximum number of photos to return (default: all, max: 500)
	 * - offset: Number of photos to skip (default: 0)
	 * - sort: Sort order for photos ('asc' = oldest first, 'desc' = newest first, default: album setting)
	 *
	 * @param EmbededRequest $request The HTTP request
	 *
	 * @return EmbedAlbumResource The album data formatted for embedding
	 *
	 * @throws NotFoundHttpException     if album doesn't exist
	 * @throws AccessDeniedHttpException if album is not publicly accessible
	 */
	public function getAlbum(EmbededRequest $request): EmbedAlbumResource
	{
		/** @var Album $album */
		$album = $request->album() ?? throw new LycheeLogicException('Album should be set in EmbededRequest');

		$this->loadAlbumPhotos($album, $request->limit, $request->offset, $request->sort, $request->authors);

		return EmbedAlbumResource::fromModel($album);
	}

	/**
	 * Get public photo stream for embedding on external sites.
	 *
	 * Returns all public photos (photos visible in the public RSS feed) for embedding.
	 * This includes only photos from albums that are:
	 * - Public (is_public = true)
	 * - Not password protected
	 * - Not link-required only
	 *
	 * Photos are ordered by EXIF taken_at (with fallback to created_at), most recent first by default.
	 *
	 * Supports optional pagination via query parameters:
	 * - limit: Maximum number of photos to return (default: 100, max: 500)
	 * - offset: Number of photos to skip (default: 0)
	 * - sort: Sort order for photos ('asc' = oldest first, 'desc' = newest first, default: 'desc')
	 *
	 * @param EmbededRequest $request The HTTP request
	 *
	 * @return EmbedStreamResource The public photos formatted for embedding
	 */
	public function getPublicStream(EmbededRequest $request): EmbedStreamResource
	{
		$photos = $this->findPublicPhotos($request->limit ?? 100, $request->offset, $request->sort ?? 'desc', $request->authors);

		// Get site title from configuration
		$site_title = strval($request->configs()->getValue('site_title') ?? 'Lychee');

		return EmbedStreamResource::fromPhotos($site_title, $photos, !request()->configs()->getValueAsBool('grants_full_photo_access'));
	}

	/**
	 * Find all public photos (photos visible in RSS feed).
	 *
	 * Uses PhotoQueryPolicy to filter photos based on public accessibility.
	 * Only includes photos from albums that are browsable without authentication.
	 *
	 * @param int           $limit   Maximum number of photos to return
	 * @param int           $offset  Number of photos to skip
	 * @param string        $sort    Sort order ('asc' or 'desc')
	 * @param string[]|null $authors Optional usernames to filter photos by uploader
	 *
	 * @return \Illuminate\Support\Collection Collection of Photo models with size_variants loaded
	 */
	private function findPublicPhotos(int $limit, int $offset, string $sort, ?array $authors = null): \Illuminate\Support\Collection
	{
		$user = Auth::user();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		// Start with base photo query
		$photos_query = Photo::query();

		// Apply security filter to only include searchable photos
		// (photos in public albums without password/link restrictions)
		// No origin album context (null) means search across all albums
		$this->photo_query_policy->applySearchabilityFilter(
			query: $photos_query,
			user: $user,
			unlocked_album_ids: $unlocked_album_ids,
			origin: null,
			include_nsfw: !request()->configs()->getValueAsBool('hide_nsfw_in_rss')
		);

		// Filter by author (uploader username) if specified
		$photos_query->when($authors !== null, fn ($q) => $q->whereHas('owner', fn ($q2) => $q2->whereIn('username', $authors)));

		// Order by EXIF taken_at (with fallback to created_at) with specified sort order
		// Convert string to enum
		$order_enum = $sort === 'asc' ? OrderSortingType::ASC : OrderSortingType::DESC;
		$photos_query->orderByRaw('COALESCE(taken_at, created_at) ' . $order_enum->value);

		// Apply pagination
		$photos_query->skip($offset)->take($limit);

		// Eager load photos with size variants (avoids N+1 query problem)
		$photos = $photos_query->with('size_variants')->get();

		return $photos;
	}

	/**
	 * Load photos for an album with pagination and sorting.
	 *
	 * This method hydrates the album's photos relation with the requested
	 * photos, applying pagination and sorting. It also sets the photos_count
	 * attribute to the total number of photos in the album.
	 *
	 * @param BaseAlbum     $album   The album to load photos for
	 * @param int|null      $limit   Maximum number of photos to load (null = all)
	 * @param int           $offset  Number of photos to skip
	 * @param string|null   $sort    Sort order override ('asc' or 'desc', null = use album default)
	 * @param string[]|null $authors Optional usernames to filter photos by uploader
	 */
	private function loadAlbumPhotos(BaseAlbum $album, ?int $limit = null, int $offset = 0, ?string $sort = null, ?array $authors = null): void
	{
		$total_photos = $album->photos()->getQuery()
			->when($authors !== null, fn ($q) => $q->whereHas('owner', fn ($q2) => $q2->whereIn('username', $authors)))
			->count();

		$photos_query = $album->photos()->getQuery()
			->when($authors !== null, fn ($q) => $q->whereHas('owner', fn ($q2) => $q2->whereIn('username', $authors)));

		// Apply pagination if requested
		if ($limit !== null) {
			$photos_query->skip($offset)->take($limit);
		}
		$photos_query->with('size_variants');

		// Use custom sort order if provided, otherwise use album's default sorting
		if ($sort !== null) {
			// Override with custom sort by EXIF taken_at (with fallback to created_at)
			// Convert string to enum
			$order_enum = $sort === 'asc' ? OrderSortingType::ASC : OrderSortingType::DESC;
			$photos = (new SortingDecorator($photos_query))
				->orderPhotosBy(ColumnSortingType::TAKEN_AT, $order_enum)
				->get();
		} else {
			// Use album's configured sorting
			$sorting = $album->getEffectivePhotoSorting();
			$photos = (new SortingDecorator($photos_query))
				->orderPhotosBy($sorting->column, $sorting->order)
				->get();
		}

		// Replace the photos relation with the paginated results
		$album->setRelation('photos', $photos);
		$album->setAttribute('photos_count', $total_photos);
	}
}
