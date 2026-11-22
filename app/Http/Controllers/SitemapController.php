<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\Sitemap\ListSitemapAlbums;
use App\Actions\Sitemap\SitemapRenderer;
use App\Repositories\ConfigManager;
use App\Services\GalleryLockState;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * `GET /sitemap.xml` (Feature 117, API-117-01, ADR-117-01).
 *
 * 404 while the sitemap is off, login is required or a gallery password is
 * set. Otherwise the guest-browsable albums, rendered against the current
 * root URL from cached rows.
 */
class SitemapController extends Controller
{
	/** Crawlers and proxies may reuse the document for an hour. */
	public const MAX_AGE = 3600;

	/**
	 * @throws NotFoundHttpException
	 */
	public function __invoke(
		GalleryLockState $gallery_lock,
		ConfigManager $config_manager,
		ListSitemapAlbums $list_sitemap_albums,
		SitemapRenderer $renderer,
	): Response {
		if (!$gallery_lock->isSitemapEnabled()) {
			throw new NotFoundHttpException();
		}

		$rows = $list_sitemap_albums->do(
			$config_manager->getValueAsBool('sitemap_root_only'),
			$config_manager->getValueAsBool('hide_nsfw_in_sitemap'),
		);
		$xml = $renderer->render(
			route('gallery'),
			fn (string $album): string => route('gallery', ['albumId' => $album]),
			$rows,
			time(),
		);

		return new Response($xml, 200, [
			'Content-Type' => 'application/xml; charset=UTF-8',
			'Cache-Control' => 'public, max-age=' . self::MAX_AGE,
		]);
	}
}
