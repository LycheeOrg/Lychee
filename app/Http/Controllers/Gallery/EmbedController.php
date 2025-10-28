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
	 * @param string $albumId The album ID to embed
	 *
	 * @return EmbedAlbumResource The album data formatted for embedding
	 *
	 * @throws NotFoundHttpException     if album doesn't exist
	 * @throws AccessDeniedHttpException if album is not publicly accessible
	 */
	public function getAlbum(string $albumId): EmbedAlbumResource
	{
		$album = $this->findAlbum($albumId);

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
	 * @param string $albumId The album ID
	 *
	 * @return BaseAlbum The album instance
	 *
	 * @throws NotFoundHttpException if album doesn't exist
	 */
	private function findAlbum(string $albumId): BaseAlbum
	{
		/** @var Album|null $album */
		$album = Album::query()
			->with(['photos', 'photos.size_variants'])
			->find($albumId);

		if ($album === null) {
			\Log::info('Embed album not found', [
				'album_id' => $albumId,
				'ip' => request()->ip(),
			]);
			throw new NotFoundHttpException('Album not found');
		}

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
