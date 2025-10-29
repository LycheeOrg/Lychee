<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\Embed\EmbedAlbumResource;
use App\Http\Resources\Embed\EmbedStreamResource;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
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
	 * - limit: Maximum number of photos to return (default: all, max: 100)
	 * - offset: Number of photos to skip (default: 0)
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

		// Validate and cap limit to 100 max
		if ($limit !== null) {
			$limit = max(1, min((int) $limit, 100));
		}
		$offset = max(0, (int) $offset);

		$album = $this->findAlbum($albumId, $limit, $offset);

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
	 * Photos are ordered by creation date (most recent first).
	 *
	 * Supports optional pagination via query parameters:
	 * - limit: Maximum number of photos to return (default: 100, max: 100)
	 * - offset: Number of photos to skip (default: 0)
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

		// Validate and cap limit to 100 max
		$limit = max(1, min((int) $limit, 100));
		$offset = max(0, (int) $offset);

		$photos = $this->findPublicPhotos($limit, $offset);

		// Get site title from configuration
		$siteTitle = Configs::getValueAsString('site_title', 'Lychee');

		return EmbedStreamResource::fromPhotos($siteTitle, $photos);
	}

	/**
	 * Find all public photos (photos visible in RSS feed).
	 *
	 * Uses PhotoQueryPolicy to filter photos based on public accessibility.
	 * Only includes photos from albums that are browsable without authentication.
	 *
	 * @param int $limit  Maximum number of photos to return
	 * @param int $offset Number of photos to skip
	 *
	 * @return \Illuminate\Support\Collection Collection of Photo models with size_variants loaded
	 */
	private function findPublicPhotos(int $limit, int $offset): \Illuminate\Support\Collection
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

		// Order by most recent first
		$photosQuery->orderBy('created_at', 'desc');

		// Apply pagination
		$photosQuery->skip($offset)->take($limit);

		// Eager load photos with size variants (avoids N+1 query problem)
		$photos = $photosQuery->with('size_variants')->get();

		return $photos;
	}

	/**
	 * Find album by ID with photos and size variants loaded.
	 *
	 * @param string   $albumId The album ID
	 * @param int|null $limit   Maximum number of photos to load (null = all)
	 * @param int      $offset  Number of photos to skip
	 *
	 * @return BaseAlbum The album instance
	 *
	 * @throws NotFoundHttpException if album doesn't exist
	 */
	private function findAlbum(string $albumId, ?int $limit = null, int $offset = 0): BaseAlbum
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

		// Load total photo count before pagination
		$album->loadCount('photos');

		// Build query for photos with size variants
		$photosQuery = $album->photos()->getQuery();

		// Apply pagination if requested
		if ($limit !== null) {
			$photosQuery->skip($offset)->take($limit);
		}

		// Eager load photos with size variants (avoids N+1 query problem)
		$photos = $photosQuery->with('size_variants')->get();

		// Replace the photos relation with the paginated results
		$album->setRelation('photos', $photos);

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
