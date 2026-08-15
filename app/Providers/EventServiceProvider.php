<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Providers;

use App\Events\AccessPermissionChanged;
use App\Events\AlbumChildrenChanged;
use App\Events\AlbumComputedDataUpdated;
use App\Events\AlbumDeleted;
use App\Events\AlbumListingCacheFlushRequested;
use App\Events\AlbumSaved;
use App\Events\AlbumTagsChanged;
use App\Events\BaseAlbumRemoved;
use App\Events\Metrics\AlbumDownload;
use App\Events\Metrics\AlbumShared;
use App\Events\Metrics\AlbumVisit;
use App\Events\Metrics\PhotoDownload;
use App\Events\Metrics\PhotoFavourite;
use App\Events\Metrics\PhotoShared;
use App\Events\Metrics\PhotoVisit;
use App\Events\OrderCompleted;
use App\Events\PersonAlbumSaved;
use App\Events\PhotoAdded;
use App\Events\PhotoDeleted;
use App\Events\PhotoHighlightToggled;
use App\Events\PhotoMoved;
use App\Events\PhotoPersonsChanged;
use App\Events\PhotoRatingChanged;
use App\Events\PhotoSaved;
use App\Events\PhotoTagsChanged;
use App\Events\PhotoWillBeDeleted;
use App\Events\TagAlbumSaved;
use App\Events\UserGroupMembershipChanged;
use App\Listeners\CacheListener;
use App\Listeners\LogQueryTimeout;
use App\Listeners\ManagedCacheAlbumListingInvalidator;
use App\Listeners\ManagedCacheUserListingInvalidator;
use App\Listeners\MetricsListener;
use App\Listeners\OrderCompletedListener;
use App\Listeners\RecomputeAlbumSizeOnAlbumChange;
use App\Listeners\RecomputeAlbumSizeOnPhotoMutation;
use App\Listeners\RecomputeAlbumStatsOnAlbumChange;
use App\Listeners\RecomputeAlbumStatsOnPhotoChange;
use App\Listeners\RecomputeAlbumUserThumbsOnPhotoChange;
use App\Listeners\RotateLicenseKeyOnLogin;
use App\Listeners\WebhookListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Database\Events\QueryExecuted;
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

		// Log slow/timeout SQL queries when DB_LOG_SQL is enabled
		// @codeCoverageIgnoreStart
		if (config('database.db_log_sql', false) === true) {
			Event::listen(QueryExecuted::class, LogQueryTimeout::class . '@handle');
		}
		// @codeCoverageIgnoreEnd

		Event::listen(AlbumDownload::class, MetricsListener::class . '@handle');
		Event::listen(AlbumShared::class, MetricsListener::class . '@handle');
		Event::listen(AlbumVisit::class, MetricsListener::class . '@handle');
		Event::listen(PhotoDownload::class, MetricsListener::class . '@handle');
		Event::listen(PhotoFavourite::class, MetricsListener::class . '@handle');
		Event::listen(PhotoShared::class, MetricsListener::class . '@handle');
		Event::listen(PhotoVisit::class, MetricsListener::class . '@handle');

		Event::listen(OrderCompleted::class, OrderCompletedListener::class . '@handle');

		Event::listen(PhotoSaved::class, RecomputeAlbumStatsOnPhotoChange::class . '@handlePhotoSaved');
		Event::listen(PhotoDeleted::class, RecomputeAlbumStatsOnPhotoChange::class . '@handlePhotoDeleted');
		Event::listen(AlbumSaved::class, RecomputeAlbumStatsOnAlbumChange::class . '@handleAlbumSaved');
		Event::listen(AlbumDeleted::class, RecomputeAlbumStatsOnAlbumChange::class . '@handleAlbumDeleted');

		Event::listen(PhotoSaved::class, RecomputeAlbumSizeOnPhotoMutation::class . '@handlePhotoSaved');
		Event::listen(PhotoDeleted::class, RecomputeAlbumSizeOnPhotoMutation::class . '@handlePhotoDeleted');
		Event::listen(AlbumSaved::class, RecomputeAlbumSizeOnAlbumChange::class . '@handleAlbumSaved');
		Event::listen(AlbumDeleted::class, RecomputeAlbumSizeOnAlbumChange::class . '@handleAlbumDeleted');

		Event::listen(PhotoSaved::class, RecomputeAlbumUserThumbsOnPhotoChange::class . '@handlePhotoSaved');
		Event::listen(PhotoWillBeDeleted::class, RecomputeAlbumUserThumbsOnPhotoChange::class . '@handlePhotoWillBeDeleted');
		Event::listen(PhotoMoved::class, RecomputeAlbumUserThumbsOnPhotoChange::class . '@handlePhotoMoved');
		Event::listen(PhotoHighlightToggled::class, RecomputeAlbumUserThumbsOnPhotoChange::class . '@handlePhotoHighlightToggled');
		Event::listen(PhotoRatingChanged::class, RecomputeAlbumUserThumbsOnPhotoChange::class . '@handlePhotoRatingChanged');
		Event::listen(PhotoTagsChanged::class, RecomputeAlbumUserThumbsOnPhotoChange::class . '@handlePhotoTagsChanged');
		Event::listen(PhotoPersonsChanged::class, RecomputeAlbumUserThumbsOnPhotoChange::class . '@handlePhotoPersonsChanged');

		Event::listen(Login::class, RotateLicenseKeyOnLogin::class . '@handle');

		// Webhook dispatch for photo lifecycle events
		Event::listen(PhotoAdded::class, WebhookListener::class . '@handlePhotoAdded');
		Event::listen(PhotoMoved::class, WebhookListener::class . '@handlePhotoMoved');
		Event::listen(PhotoWillBeDeleted::class, WebhookListener::class . '@handlePhotoWillBeDeleted');

		// Managed-cache album-listing invalidation (Feature 053)
		Event::listen(AlbumSaved::class, ManagedCacheAlbumListingInvalidator::class . '@handleAlbumSaved');
		Event::listen(AlbumDeleted::class, ManagedCacheAlbumListingInvalidator::class . '@handleAlbumDeleted');
		Event::listen(AlbumChildrenChanged::class, ManagedCacheAlbumListingInvalidator::class . '@handleAlbumChildrenChanged');
		Event::listen(TagAlbumSaved::class, ManagedCacheAlbumListingInvalidator::class . '@handleTagAlbumSaved');
		Event::listen(PersonAlbumSaved::class, ManagedCacheAlbumListingInvalidator::class . '@handlePersonAlbumSaved');
		Event::listen(BaseAlbumRemoved::class, ManagedCacheAlbumListingInvalidator::class . '@handleBaseAlbumRemoved');
		Event::listen(AccessPermissionChanged::class, ManagedCacheAlbumListingInvalidator::class . '@handleAccessPermissionChanged');
		Event::listen(AlbumComputedDataUpdated::class, ManagedCacheAlbumListingInvalidator::class . '@handleAlbumComputedDataUpdated');
		Event::listen(AlbumListingCacheFlushRequested::class, ManagedCacheAlbumListingInvalidator::class . '@handleAlbumListingCacheFlushRequested');
		Event::listen(AlbumTagsChanged::class, ManagedCacheAlbumListingInvalidator::class . '@handleAlbumTagsChanged');

		// Managed-cache user-listing invalidation (Feature 053)
		Event::listen(UserGroupMembershipChanged::class, ManagedCacheUserListingInvalidator::class . '@handle');
	}
}
