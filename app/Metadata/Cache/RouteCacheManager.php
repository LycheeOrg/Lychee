<?php

namespace App\Metadata\Cache;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\CacheTag;
use App\Exceptions\Internal\LycheeLogicException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
			'api/v2/Album' => new RouteCacheConfig(tag: CacheTag::GALLERY, user_dependant: true, extra: [RequestAttribute::ALBUM_ID_ATTRIBUTE]),
			'api/v2/Album::getTargetListAlbums' => false, // TODO: cache me later.
			'api/v2/Albums' => new RouteCacheConfig(tag: CacheTag::GALLERY, user_dependant: true),
			'api/v2/Auth::config' => new RouteCacheConfig(tag: CacheTag::SETTINGS, user_dependant: true),
			'api/v2/Auth::rights' => new RouteCacheConfig(tag: CacheTag::SETTINGS, user_dependant: true),
			'api/v2/Auth::user' => new RouteCacheConfig(tag: CacheTag::USER, user_dependant: true),
			'api/v2/Diagnostics' => false,
			'api/v2/Diagnostics::config' => false,
			'api/v2/Diagnostics::info' => new RouteCacheConfig(tag: CacheTag::SETTINGS, user_dependant: true),
			'api/v2/Diagnostics::permissions' => new RouteCacheConfig(tag: CacheTag::SETTINGS, user_dependant: true),
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

			'api/v2/Map' => new RouteCacheConfig(tag: CacheTag::GALLERY, user_dependant: true, extra: [RequestAttribute::ALBUM_ID_ATTRIBUTE]),
			'api/v2/Map::provider' => new RouteCacheConfig(tag: CacheTag::SETTINGS),
			'api/v2/Oauth' => new RouteCacheConfig(tag: CacheTag::USER, user_dependant: true),

			// Response must be different for each call.
			'api/v2/Photo::random' => false,

			'api/v2/Search' => false, // TODO: how to support pagination ?? new RouteCacheConfig(tag: CacheTag::GALLERY, user_dependant: true, extra: ['album_id', 'terms']),
			'api/v2/Search::init' => false,
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
			'api/v2/WebAuthn' => false,

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
		$key = self::REQUEST . $request->route()->uri;

		// If the request is user dependant, we add the user id to the key.
		// That way we ensure that this does not contaminate between logged in and looged out users.
		if ($config->user_dependant) {
			$key .= self::USER . Auth::id();
		}

		if (count($config->extra) > 0) {
			$key .= self::EXTRA;
			foreach ($config->extra as $extra) {
				/** @var string $vals */
				$vals = $request->input($extra) ?? '';
				$key .= ':' . $vals;
			}
		}

		return $key;
	}

	/**
	 * Generate a key for the cache.
	 *
	 * @param string               $uri
	 * @param int|null             $userId
	 * @param array<string,string> $extras
	 * @param ?RouteCacheConfig    $config
	 *
	 * @return string
	 */
	public function gen_key(
		string $uri,
		?int $userId = null,
		array $extras = [],
		?RouteCacheConfig $config = null,
	): string {
		$config ??= $this->cache_list[$uri] ?? throw new LycheeLogicException('No cache config for ' . $uri);

		if ($config === false) {
			throw new LycheeLogicException($uri . ' is not supposed to be cached.');
		}

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
	 *
	 * @return string[]
	 */
	public function retrieve_keys_for_tag(CacheTag $tag, bool $with_extra = false, bool $without_extra = false): array
	{
		$keys = [];
		foreach ($this->cache_list as $uri => $value) {
			if (
				$value !== false &&
				$value->tag === $tag &&
				// Either with extra is set to false => ignore condition
				// Or with extra is set to true and we have extra parameters => ignore condition
				($with_extra === false || count($value->extra) > 0) &&
				($without_extra === false || count($value->extra) === 0)
			) {
				$keys[] = $uri;
			}
		}

		return $keys;
	}
}
