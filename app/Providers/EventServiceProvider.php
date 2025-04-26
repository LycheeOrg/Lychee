<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Providers;

use App\Events\AlbumRouteCacheUpdated;
use App\Events\Metrics\AlbumDownload;
use App\Events\Metrics\AlbumShared;
use App\Events\Metrics\AlbumVisit;
use App\Events\Metrics\PhotoDownload;
use App\Events\Metrics\PhotoFavourite;
use App\Events\Metrics\PhotoShared;
use App\Events\Metrics\PhotoVisit;
use App\Events\TaggedRouteCacheUpdated;
use App\Listeners\AlbumCacheCleaner;
use App\Listeners\CacheListener;
use App\Listeners\MetricsListener;
use App\Listeners\TaggedRouteCacheCleaner;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Amazon\AmazonExtendSocialite;
use SocialiteProviders\Apple\AppleExtendSocialite;
use SocialiteProviders\Authelia\AutheliaExtendSocialite;
use SocialiteProviders\Authentik\AuthentikExtendSocialite;
use SocialiteProviders\Facebook\FacebookExtendSocialite;
use SocialiteProviders\GitHub\GitHubExtendSocialite;
use SocialiteProviders\Google\GoogleExtendSocialite;
use SocialiteProviders\Keycloak\KeycloakExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;
use SocialiteProviders\Nextcloud\NextcloudExtendSocialite;

class EventServiceProvider extends ServiceProvider
{
	/**
	 * The event listener mappings for the application.
	 *
	 * @var array<string,array<int,string>>
	 */
	protected $listen = [
		Registered::class => [
			// SendEmailVerificationNotification::class,
		],
		SocialiteWasCalled::class => [
			AmazonExtendSocialite::class . '@handle',
			AppleExtendSocialite::class . '@handle',
			AutheliaExtendSocialite::class . '@handle',
			AuthentikExtendSocialite::class . '@handle',
			FacebookExtendSocialite::class . '@handle',
			GitHubExtendSocialite::class . '@handle',
			GoogleExtendSocialite::class . '@handle',
			// Mastodon is provided directly.
			MicrosoftExtendSocialite::class . '@handle',
			NextcloudExtendSocialite::class . '@handle',
			KeycloakExtendSocialite::class . '@handle',
		],
	];

	/**
	 * Register any events for your application.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		Event::listen(CacheHit::class, CacheListener::class . '@handle');
		Event::listen(CacheMissed::class, CacheListener::class . '@handle');
		Event::listen(KeyForgotten::class, CacheListener::class . '@handle');
		Event::listen(KeyWritten::class, CacheListener::class . '@handle');

		Event::listen(AlbumRouteCacheUpdated::class, AlbumCacheCleaner::class . '@handle');
		Event::listen(TaggedRouteCacheUpdated::class, TaggedRouteCacheCleaner::class . '@handle');

		Event::listen(AlbumDownload::class, MetricsListener::class . '@handle');
		Event::listen(AlbumShared::class, MetricsListener::class . '@handle');
		Event::listen(AlbumVisit::class, MetricsListener::class . '@handle');
		Event::listen(PhotoDownload::class, MetricsListener::class . '@handle');
		Event::listen(PhotoFavourite::class, MetricsListener::class . '@handle');
		Event::listen(PhotoShared::class, MetricsListener::class . '@handle');
		Event::listen(PhotoVisit::class, MetricsListener::class . '@handle');
	}
}
