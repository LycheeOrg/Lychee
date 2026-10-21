<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Repositories\ConfigManager;
use Illuminate\Http\Request;

/**
 * {@see ResolveConfigs} for the binary routes (`/Asset`,
 * `/Faces/{face_id}/photo/{variant}`): loads only the keys those requests
 * read (Feature 102, FR-102-03, ADR-102-02).
 *
 * Not a subclass of {@see ResolveConfigs}: the routes exclude that one with
 * `withoutMiddleware()`, which also strips subclasses.
 */
class ResolveAssetConfigs
{
	/**
	 * Every key read while serving the binary routes, recorded across their
	 * test suites and completed with the smart, tag and person album
	 * membership reads. Reading a key outside this list throws.
	 */
	public const KEYS = [
		// SetLocale
		'lang',
		// Gallery lock (`gallery_password` middleware)
		'gallery_password',
		// Temporary image links
		'temporary_image_link_enabled',
		'temporary_image_link_life_in_seconds',
		'temporary_image_link_when_admin',
		'temporary_image_link_when_logged_in',
		// Watermarking
		'imagick',
		'watermark_enabled',
		'watermark_logged_in_users_enabled',
		'watermark_original',
		'watermark_photo_id',
		'watermark_public',
		// Tag albums
		'TA_override_visibility',
		'hide_nsfw_in_tag_albums',
		'sorting_photos_col',
		'sorting_photos_order',
		// Smart albums and timeline
		'SA_override_visibility',
		'enable_smart_album_per_owner',
		'hide_nsfw_in_smart_albums',
		'hide_nsfw_in_timeline',
		'recent_age',
		'best_pictures_count',
		'my_best_pictures_count',
		'photos_star_visibility',
		'timeline_page_enabled',
		'timeline_photos_public',
		// AI Vision (person albums, faces route)
		'ai_vision_enabled',
		'ai_vision_face_enabled',
		'ai_vision_face_permission_mode',
	];

	public function handle(Request $request, \Closure $next): mixed
	{
		$config = resolve(ConfigManager::class);

		// Not installed yet: same pass-through as ResolveConfigs.
		if (count($config->loadOnly(self::KEYS)) === 0) {
			return $next($request);
		}

		$request->attributes->set('configs', (new ResolveConfigs())->resolve_configs($config));

		return $next($request);
	}
}
