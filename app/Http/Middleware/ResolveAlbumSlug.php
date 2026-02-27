<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Constants\RandomID;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\SmartAlbumType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that translates album slugs to real album IDs.
 *
 * For each `album_id` value (query param, JSON body, or route param `albumId`),
 * if the value is not a 24-char random ID and not a SmartAlbumType value,
 * the middleware queries `base_albums.slug` and replaces the value with the real ID.
 * If no match is found, the value passes through unchanged (downstream returns 404).
 */
class ResolveAlbumSlug
{
	/**
	 * Handle an incoming request.
	 */
	public function handle(Request $request, \Closure $next): Response
	{
		// Normal album operations
		$this->resolveQueryOrBodyParam($request, RequestAttribute::ALBUM_ID_ATTRIBUTE);
		// Some photo/album operations rely on parent_id
		$this->resolveQueryOrBodyParam($request, RequestAttribute::PARENT_ID_ATTRIBUTE);
		// Some album/photos operations rely on from_id
		$this->resolveQueryOrBodyParam($request, RequestAttribute::FROM_ID_ATTRIBUTE);

		// Used mostly in routes such as first loading of album
		$this->resolveRouteParam($request, 'albumId');

		// May occur but unlikely, better safe than sorry.
		$this->resolveArrayParam($request, RequestAttribute::ALBUM_IDS_ATTRIBUTE);

		return $next($request);
	}

	/**
	 * Resolve a single album_id from query string or JSON body.
	 */
	private function resolveQueryOrBodyParam(Request $request, string $param_name): void
	{
		$value = $request->input($param_name);
		if (!is_string($value) || $value === '') {
			return;
		}

		$resolved_id = $this->resolveSlug($value);
		if ($resolved_id !== null) {
			$request->merge([$param_name => $resolved_id]);
		}
	}

	/**
	 * Resolve an albumId route parameter.
	 */
	private function resolveRouteParam(Request $request, string $param_name): void
	{
		$route = $request->route();
		if ($route === null) {
			return;
		}

		$value = $route->parameter($param_name);
		if (!is_string($value) || $value === '') {
			return;
		}

		$resolved_id = $this->resolveSlug($value);
		if ($resolved_id !== null) {
			$route->setParameter($param_name, $resolved_id);
		}
	}

	/**
	 * Resolve an array of album IDs (e.g., album_ids for batch endpoints).
	 */
	private function resolveArrayParam(Request $request, string $param_name): void
	{
		$values = $request->input($param_name);
		if (!is_array($values)) {
			return;
		}

		$changed = false;
		foreach ($values as $index => $value) {
			if (!is_string($value) || $value === '') {
				continue;
			}

			$resolved_id = $this->resolveSlug($value);
			if ($resolved_id !== null) {
				$values[$index] = $resolved_id;
				$changed = true;
			}
		}

		if ($changed) {
			$request->merge([$param_name => $values]);
		}
	}

	/**
	 * Attempt to resolve a slug to a real album ID.
	 *
	 * Returns the real ID if the value is a slug that matches a database record.
	 * Returns null if the value is already a real ID, SmartAlbumType, or no match is found.
	 */
	private function resolveSlug(string $value): ?string
	{
		// 24-char random IDs pass through without any DB query
		if (strlen($value) === RandomID::ID_LENGTH) {
			return null;
		}

		// SmartAlbumType values pass through without any DB query
		if (SmartAlbumType::tryFrom($value) !== null) {
			return null;
		}

		// Look up the slug in the database using raw query to avoid
		// Eloquent eager loading (BaseAlbumImpl has $with = ['owner', ...])
		/** @var string|null $real_id */
		$real_id = DB::table('base_albums')
			->where('slug', '=', $value)
			->value('id');

		return $real_id;
	}
}
