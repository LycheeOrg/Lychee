<?php

namespace App\Metadata\Cache;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final readonly class RouteCacheManager
{
	public const REQUEST = 'R:';
	public const USER = '|U:';
	public const EXTRA = '|E:';

	/** @var array<string,false|RouteCacheConfig> */
	private array $cache_list;

	/**
	 * Initalize the cache list.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->cache_list = [
			'api/v2/Album' => new RouteCacheConfig(tag: 'gallery', user_dependant: true, extra: ['album_id']),
			'api/v2/Album::getTargetListAlbums' => false, // TODO: cache me later.
			'api/v2/Albums' => new RouteCacheConfig(tag: 'gallery', user_dependant: true),
			'api/v2/Auth::config' => new RouteCacheConfig(tag: 'auth', user_dependant: true),
			'api/v2/Auth::rights' => new RouteCacheConfig(tag: 'auth', user_dependant: true),
			'api/v2/Auth::user' => new RouteCacheConfig(tag: 'user', user_dependant: true),
			'api/v2/Diagnostics' => false,
			'api/v2/Diagnostics::config' => false,
			'api/v2/Diagnostics::info' => new RouteCacheConfig(tag: 'settings', user_dependant: true),
			'api/v2/Diagnostics::permissions' => new RouteCacheConfig(tag: 'settings', user_dependant: true),
			'api/v2/Diagnostics::space' => new RouteCacheConfig(tag: 'settings', user_dependant: true),

			// Response must be different for each call.
			'api/v2/Frame' => false,

			'api/v2/Gallery::Footer' => new RouteCacheConfig(tag: 'settings'),
			'api/v2/Gallery::Init' => new RouteCacheConfig(tag: 'settings'),
			'api/v2/Gallery::getLayout' => new RouteCacheConfig(tag: 'settings'),
			'api/v2/Gallery::getUploadLimits' => new RouteCacheConfig(tag: 'settings'),

			'api/v2/Jobs' => false, // TODO: fix me later
			'api/v2/LandingPage' => new RouteCacheConfig(tag: 'settings'),

			// We do not need to cache those.
			'api/v2/Maintenance::cleaning' => false,
			'api/v2/Maintenance::fullTree' => false,
			'api/v2/Maintenance::genSizeVariants' => false,
			'api/v2/Maintenance::jobs' => false,
			'api/v2/Maintenance::missingFileSize' => false,
			'api/v2/Maintenance::tree' => false,
			'api/v2/Maintenance::update' => false,

			'api/v2/Map' => new RouteCacheConfig(tag: 'gallery', user_dependant: true, extra: ['album_id']),
			'api/v2/Map::provider' => new RouteCacheConfig(tag: 'settings'),
			'api/v2/Oauth' => new RouteCacheConfig(tag: 'user', user_dependant: true),

			// Response must be different for each call.
			'api/v2/Photo::random' => false,

			'api/v2/Search' => false, // TODO: how to support pagination ?? new RouteCacheConfig(tag: 'gallery', user_dependant: true, extra: ['album_id', 'terms']),
			'api/v2/Search::init' => new RouteCacheConfig(tag: 'settings'),
			'api/v2/Settings' => new RouteCacheConfig(tag: 'settings', user_dependant: true),
			'api/v2/Settings::getLanguages' => new RouteCacheConfig(tag: 'settings'),
			'api/v2/Sharing' => new RouteCacheConfig(tag: 'gallery', user_dependant: true, extra: ['album_id']),
			'api/v2/Sharing::all' => new RouteCacheConfig(tag: 'gallery', user_dependant: true),
			'api/v2/Statistics::albumSpace' => new RouteCacheConfig(tag: 'statistics', user_dependant: true),
			'api/v2/Statistics::sizeVariantSpace' => new RouteCacheConfig(tag: 'statistics', user_dependant: true),
			'api/v2/Statistics::totalAlbumSpace' => new RouteCacheConfig(tag: 'statistics', user_dependant: true),
			'api/v2/Statistics::userSpace' => new RouteCacheConfig(tag: 'statistics', user_dependant: true),
			'api/v2/UserManagement' => new RouteCacheConfig(tag: 'users', user_dependant: true),
			'api/v2/Users' => new RouteCacheConfig(tag: 'users', user_dependant: true),
			'api/v2/Users::count' => new RouteCacheConfig(tag: 'users', user_dependant: true),
			'api/v2/Version' => false,
			'api/v2/WebAuthn' => false,

			// This is returning a stream, we do not cache it.
			'api/v2/Zip' => false,
		];
	}

	public function getConfig(string $uri): RouteCacheConfig|false
	{
		if (!array_key_exists($uri, $this->cache_list)) {
			Log::warning('ResponseCache: No cache config for ' . $uri);

			return false;
		}

		return $this->cache_list[$uri];
	}

	public function getKey(Request $request, RouteCacheConfig $config): string
	{
		$key = self::REQUEST . $request->route()->uri;

		// If the request is user dependant, we add the user id to the key.
		// That way we ensure that this does not contaminate between logged in and looged out users.
		if ($config->user_dependant) {
			$key .= self::USER . $request->user?->getId();
		}

		if (count($config->extra) > 0) {
			$key .= self::EXTRA;
			foreach ($config->extra as $extra) {
				/** @var string $vals */
				$vals = $request->query($extra) ?? '';
				$key .= ':' . $vals;
			}
		}

		return $key;
	}

	/**
	 * Generate a key for the cache.
	 *
	 * @param RouteCacheConfig     $config
	 * @param string               $uri
	 * @param int|null             $userId
	 * @param array<string,string> $extras
	 *
	 * @return string
	 */
	public function genKey(RouteCacheConfig $config, string $uri, ?int $userId, array $extras): string
	{
		$key = self::REQUEST . $uri;

		// If the request is user dependant, we add the user id to the key.
		// That way we ensure that this does not contaminate between logged in and looged out users.
		if ($config->user_dependant) {
			$key .= self::USER . $userId;
		}

		if (count($config->extra) > 0) {
			$key .= self::EXTRA;
			foreach ($config->extra as $extra) {
				$key .= ':' . ($extras[$extra] ?? '');
			}
		}

		return $key;
	}

	/**
	 * Return the tag associated to the route if there is one.
	 * Return false if there is no tag for this route or if the route is not cached (just safe precaution).
	 *
	 * @param string $uri
	 *
	 * @return string|false
	 */
	public function getTag(string $uri): string|false
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
	 * @param string $tag
	 *
	 * @return string[]
	 */
	public function retrieveKeysForTag(string $tag): array
	{
		$keys = [];
		foreach ($this->cache_list as $uri => $value) {
			if (is_array($value) && array_key_exists('tag', $value) && $value['tag'] === $tag) {
				$keys[] = $uri;
			}
		}

		return $keys;
	}
}
