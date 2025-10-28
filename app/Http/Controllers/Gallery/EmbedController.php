<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\Embed\EmbedAlbumResource;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller responsible for providing embed data for external websites.
 */
class EmbedController extends Controller
{
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

		// Build query for photos with size variants
		$photosQuery = $album->photos()->getQuery();

		// Apply pagination if requested
		if ($limit !== null) {
			$photosQuery->skip($offset)->take($limit);
		}

		// Load photos with size variants
		$photos = $photosQuery->get();
		$photos->load('size_variants');

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
