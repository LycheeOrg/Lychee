<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Enum\ColumnSortingType;
use App\Enum\OrderSortingType;
use App\Http\Resources\Embed\EmbedAlbumResource;
use App\Http\Resources\Embed\EmbedStreamResource;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller responsible for providing embed data for external websites.
 */
class EmbedController extends Controller
{
	protected PhotoQueryPolicy $photo_query_policy;

	public function __construct(PhotoQueryPolicy $photo_query_policy)
	{
		$this->photo_query_policy = $photo_query_policy;
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
	 * @param Request $request The HTTP request
	 * @param string  $albumId The album ID to embed
	 *
	 * @return EmbedAlbumResource The album data formatted for embedding
	 *
	 * @throws NotFoundHttpException     if album doesn't exist
	 * @throws AccessDeniedHttpException if album is not publicly accessible
	 */
	public function getAlbum(Request $request, string $albumId): EmbedAlbumResource
	{
		// Parse pagination parameters
		$limit = $request->query('limit', null);
		$offset = $request->query('offset', 0);
		$sort = $request->query('sort', null);

		// Validate and cap limit to 500 max
		if ($limit !== null) {
			$limit = max(1, min((int) $limit, 500));
		}
		$offset = max(0, (int) $offset);

		// Validate sort order
		if ($sort !== null && !in_array($sort, ['asc', 'desc'], true)) {
			$sort = null; // Invalid value, use default
		}

		$album = $this->findAlbum($albumId, $limit, $offset, $sort);

		// Verify album is publicly accessible
		if (!$this->isPubliclyAccessible($album)) {
			\Log::warning('Embed access denied', [
				'album_id' => $albumId,
				'ip' => request()->ip(),
				'user_agent' => request()->userAgent(),
			]);
			throw new AccessDeniedHttpException('Album must be publicly accessible for embedding');
		}

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
	 * Photos are ordered by creation date (most recent first by default).
	 *
	 * Supports optional pagination via query parameters:
	 * - limit: Maximum number of photos to return (default: 100, max: 500)
	 * - offset: Number of photos to skip (default: 0)
	 * - sort: Sort order for photos ('asc' = oldest first, 'desc' = newest first, default: 'desc')
	 *
	 * @param Request $request The HTTP request
	 *
	 * @return EmbedStreamResource The public photos formatted for embedding
	 */
	public function getPublicStream(Request $request): EmbedStreamResource
	{
		// Parse pagination parameters
		$limit = $request->query('limit', 100);
		$offset = $request->query('offset', 0);
		$sort = $request->query('sort', 'desc');

		// Validate and cap limit to 500 max
		$limit = max(1, min((int) $limit, 500));
		$offset = max(0, (int) $offset);

		// Validate sort order, default to 'desc' (newest first)
		if (!in_array($sort, ['asc', 'desc'], true)) {
			$sort = 'desc';
		}

		$photos = $this->findPublicPhotos($limit, $offset, $sort);

		// Get site title from configuration
		$siteTitle = strval(Configs::getValue('site_title') ?? 'Lychee');

		return EmbedStreamResource::fromPhotos($siteTitle, $photos);
	}

	/**
	 * Find all public photos (photos visible in RSS feed).
	 *
	 * Uses PhotoQueryPolicy to filter photos based on public accessibility.
	 * Only includes photos from albums that are browsable without authentication.
	 *
	 * @param int    $limit  Maximum number of photos to return
	 * @param int    $offset Number of photos to skip
	 * @param string $sort   Sort order ('asc' or 'desc')
	 *
	 * @return \Illuminate\Support\Collection Collection of Photo models with size_variants loaded
	 */
	private function findPublicPhotos(int $limit, int $offset, string $sort): \Illuminate\Support\Collection
	{
		// Start with base photo query
		$photosQuery = Photo::query();

		// Apply security filter to only include searchable photos
		// (photos in public albums without password/link restrictions)
		// No origin album context (null) means search across all albums
		$this->photo_query_policy->applySearchabilityFilter(
			query: $photosQuery,
			origin: null,
			include_nsfw: !Configs::getValueAsBool('hide_nsfw_in_rss')
		);

		// Order by creation date with specified sort order
		// Convert string to enum
		$orderEnum = $sort === 'asc' ? OrderSortingType::ASC : OrderSortingType::DESC;
		$photosQuery->orderBy('created_at', $orderEnum->value);

		// Apply pagination
		$photosQuery->skip($offset)->take($limit);

		// Eager load photos with size variants (avoids N+1 query problem)
		$photos = $photosQuery->with('size_variants')->get();

		return $photos;
	}

	/**
	 * Find album by ID with photos and size variants loaded.
	 *
	 * @param string      $albumId The album ID
	 * @param int|null    $limit   Maximum number of photos to load (null = all)
	 * @param int         $offset  Number of photos to skip
	 * @param string|null $sort    Sort order override ('asc' or 'desc', null = use album default)
	 *
	 * @return BaseAlbum The album instance
	 *
	 * @throws NotFoundHttpException if album doesn't exist
	 */
	private function findAlbum(string $albumId, ?int $limit = null, int $offset = 0, ?string $sort = null): BaseAlbum
	{
		/** @var Album|null $album */
		$album = Album::query()->find($albumId);

		if ($album === null) {
			\Log::info('Embed album not found', [
				'album_id' => $albumId,
				'ip' => request()->ip(),
			]);
			throw new NotFoundHttpException('Album not found');
		}

		$photosRelation = $album->photos();
		$totalPhotos = $photosRelation->count();

		$photosQuery = $album->photos()->getQuery();

		// Apply pagination if requested
		if ($limit !== null) {
			$photosQuery->skip($offset)->take($limit);
		}
		$photosQuery->with('size_variants');

		// Use custom sort order if provided, otherwise use album's default sorting
		if ($sort !== null) {
			// Override with custom sort by creation date
			// Convert string to enum
			$orderEnum = $sort === 'asc' ? OrderSortingType::ASC : OrderSortingType::DESC;
			$photos = (new SortingDecorator($photosQuery))
				->orderPhotosBy(ColumnSortingType::CREATED_AT, $orderEnum)
				->get();
		} else {
			// Use album's configured sorting
			$sorting = $album->getEffectivePhotoSorting();
			$photos = (new SortingDecorator($photosQuery))
				->orderPhotosBy($sorting->column, $sorting->order)
				->get();
		}

		// Replace the photos relation with the paginated results
		$album->setRelation('photos', $photos);
		$album->setAttribute('photos_count', $totalPhotos);

		return $album;
	}

	/**
	 * Check if album is publicly accessible for embedding.
	 *
	 * An album is embeddable if:
	 * - It has public access (is_public = true)
	 * - It doesn't require a password
	 * - It doesn't require a direct link (is_link_required = false)
	 *
	 * @param BaseAlbum $album The album to check
	 *
	 * @return bool True if album can be embedded
	 */
	private function isPubliclyAccessible(BaseAlbum $album): bool
	{
		$policy = AlbumProtectionPolicy::ofBaseAlbum($album);

		// Must be public and not require password or link-only access
		return $policy->is_public &&
			!$policy->is_password_required &&
			!$policy->is_link_required;
	}
}
