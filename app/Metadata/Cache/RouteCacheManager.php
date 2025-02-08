<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Metadata\Cache;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\CacheTag;
use App\Facades\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final readonly class RouteCacheManager
{
	public const REQUEST = 'REQ:';
	public const USER = '|USR:';
	public const EXTRA = '|EXT:';

	public const ONLY_WITH_EXTRA = 1;
	public const ONLY_WITHOUT_EXTRA = 2;

	/** @var array<string,false|RouteCacheConfig> */
	public readonly array $cache_list;

	/**
	 * Initalize the cache list.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->cache_list = [
			'api/v2/Album' => new RouteCacheConfig(tag: CacheTag::GALLERY, user_dependant: true, extra: [RequestAttribute::ALBUM_ID_ATTRIBUTE]),
			'api/v2/Album::getTargetListAlbums' => false, // TODO: cache me later.
			'api/v2/Albums' => new RouteCacheConfig(tag: CacheTag::GALLERY, user_dependant: true),
			'api/v2/Auth::config' => new RouteCacheConfig(tag: CacheTag::SETTINGS, user_dependant: true),
			'api/v2/Auth::rights' => new RouteCacheConfig(tag: CacheTag::SETTINGS, user_dependant: true),
			'api/v2/Auth::user' => new RouteCacheConfig(tag: CacheTag::USER, user_dependant: true),

			// We do not want to cache diagnostics errors and config as they are a debugging tool. The MUST represent the state of Lychee at any time.
			'api/v2/Diagnostics' => false,
			'api/v2/Diagnostics::config' => false,
			'api/v2/Diagnostics::info' => false,
			'api/v2/Diagnostics::permissions' => false,
			// We can cache the space computation because it is not changing often and very computationally heavy.
			'api/v2/Diagnostics::space' => new RouteCacheConfig(tag: CacheTag::SETTINGS, user_dependant: true),

			// Response must be different for each call.
			'api/v2/Frame' => false,

			'api/v2/Gallery::Footer' => new RouteCacheConfig(tag: CacheTag::SETTINGS),
			'api/v2/Gallery::Init' => new RouteCacheConfig(tag: CacheTag::SETTINGS),
			'api/v2/Gallery::getLayout' => new RouteCacheConfig(tag: CacheTag::SETTINGS),
			'api/v2/Gallery::getUploadLimits' => new RouteCacheConfig(tag: CacheTag::SETTINGS),

			'api/v2/Jobs' => false, // TODO: fix me later
			'api/v2/LandingPage' => new RouteCacheConfig(tag: CacheTag::SETTINGS),

			// We do not need to cache those.
			'api/v2/Maintenance::cleaning' => false,
			'api/v2/Maintenance::fullTree' => false,
			'api/v2/Maintenance::genSizeVariants' => false,
			'api/v2/Maintenance::jobs' => false,
			'api/v2/Maintenance::missingFileSize' => false,
			'api/v2/Maintenance::tree' => false,
			'api/v2/Maintenance::update' => false,
			'api/v2/Maintenance::countDuplicates' => false,
			'api/v2/Maintenance::searchDuplicates' => false,

			'api/v2/Map' => new RouteCacheConfig(tag: CacheTag::GALLERY, user_dependant: true, extra: [RequestAttribute::ALBUM_ID_ATTRIBUTE]),
			'api/v2/Map::provider' => new RouteCacheConfig(tag: CacheTag::SETTINGS),
			'api/v2/Oauth' => new RouteCacheConfig(tag: CacheTag::USER, user_dependant: true),
			'api/v2/WebAuthn' => new RouteCacheConfig(tag: CacheTag::USER, user_dependant: true),

			// Response must be different for each call.
			'api/v2/Photo::random' => false,

			// Ideally we should cache the search results, unfortunately it is not clear how to handle the pagination and the parts of the query.
			// Furthermore the result of the serach depends of the user. Making the caching strategy more complex.
			// TODO: how to support pagination ?? new RouteCacheConfig(tag: CacheTag::GALLERY, user_dependant: true, extra: ['album_id', 'terms']),
			'api/v2/Search' => false,
			'api/v2/Search::init' => new RouteCacheConfig(tag: CacheTag::SETTINGS),
			'api/v2/Settings' => new RouteCacheConfig(tag: CacheTag::SETTINGS, user_dependant: true),
			'api/v2/Settings::getLanguages' => new RouteCacheConfig(tag: CacheTag::SETTINGS),
			'api/v2/Sharing' => new RouteCacheConfig(tag: CacheTag::GALLERY, user_dependant: true, extra: [RequestAttribute::ALBUM_ID_ATTRIBUTE]),
			'api/v2/Sharing::all' => new RouteCacheConfig(tag: CacheTag::GALLERY, user_dependant: true),
			'api/v2/Statistics::albumSpace' => new RouteCacheConfig(tag: CacheTag::STATISTICS, user_dependant: true),
			'api/v2/Statistics::sizeVariantSpace' => new RouteCacheConfig(tag: CacheTag::STATISTICS, user_dependant: true),
			'api/v2/Statistics::totalAlbumSpace' => new RouteCacheConfig(tag: CacheTag::STATISTICS, user_dependant: true),
			'api/v2/Statistics::userSpace' => new RouteCacheConfig(tag: CacheTag::STATISTICS, user_dependant: true),
			'api/v2/UserManagement' => new RouteCacheConfig(tag: CacheTag::USERS, user_dependant: true),
			'api/v2/Users' => new RouteCacheConfig(tag: CacheTag::USERS, user_dependant: true),
			'api/v2/Users::count' => new RouteCacheConfig(tag: CacheTag::USERS, user_dependant: true),
			'api/v2/Version' => false,

			// This is returning a stream, we do not cache it.
			'api/v2/Zip' => false,
		];
	}

	public function get_config(string $uri): RouteCacheConfig|false
	{
		if (!array_key_exists($uri, $this->cache_list)) {
			Log::warning('ResponseCache: No cache config for ' . $uri);

			return false;
		}

		return $this->cache_list[$uri];
	}

	public function get_key(Request $request, RouteCacheConfig $config): string
	{
		$key = self::REQUEST . Helpers::getUriWithQueryString($request);

		// If the request is user dependant, we add the user id to the key.
		// That way we ensure that this does not contaminate between logged in and looged out users.
		if ($config->user_dependant) {
			$key .= self::USER . Auth::id();
		}

		return $key;
	}

	/**
	 * Return the tag associated to the route if there is one.
	 * Return false if there is no tag for this route or if the route is not cached (just safe precaution).
	 *
	 * @param string $uri
	 *
	 * @return CacheTag|false
	 */
	public function get_tag(string $uri): CacheTag|false
	{
		if (!array_key_exists($uri, $this->cache_list)) {
			return false;
		}

		if ($this->cache_list[$uri] === false) {
			return false;
		}

		return $this->cache_list[$uri]->tag;
	}

	/**
	 * Given a tag, return all the routes associated to this tag.
	 *
	 * @param CacheTag $tag
	 * @param int      $flag composed of RouteCacheManager::ONLY_WITH_EXTRA and RouteCacheManager::ONLY_WITHOUT_EXTRA
	 *
	 * @return string[]
	 */
	public function retrieve_routes_for_tag(CacheTag $tag, int $flag): array
	{
		$routes = [];
		foreach ($this->cache_list as $uri => $value) {
			if (
				$value !== false &&
				$value->tag === $tag &&
				// Either with ONLY_WITH_EXTRA flag not set => ignore condition
				// Or with ONLY_WITH_EXTRA flag set and we have extra parameters => ignore condition
				(($flag & self::ONLY_WITH_EXTRA) === 0 || count($value->extra) > 0) &&
				(($flag & self::ONLY_WITHOUT_EXTRA) === 0 || count($value->extra) === 0)
			) {
				$routes[] = $uri;
			}
		}

		return $routes;
	}
}
