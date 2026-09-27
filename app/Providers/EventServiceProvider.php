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
use App\Events\AlbumPhotoSortingChanged;
use App\Events\AlbumSaved;
use App\Events\AlbumTagsChanged;
use App\Events\BaseAlbumRemoved;
use App\Events\MapListingCacheFlushRequested;
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
use App\Events\PhotoBucketsRecomputed;
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
use App\Listeners\ManagedCacheMapListingInvalidator;
use App\Listeners\ManagedCachePhotoListingInvalidator;
use App\Listeners\ManagedCacheSearchListingInvalidator;
use App\Listeners\ManagedCacheUserListingInvalidator;
use App\Listeners\MetricsListener;
use App\Listeners\OrderCompletedListener;
use App\Listeners\PurgeAlbumUserThumbsOnMembershipChange;
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
use SocialiteProviders\Kanidm\KanidmExtendSocialite;
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
			KanidmExtendSocialite::class . '@handle',
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
		Event::listen(PhotoPersonsChanged::class, ManagedCacheAlbumListingInvalidator::class . '@handlePhotoPersonsChanged');
		Event::listen(PhotoMoved::class, ManagedCacheAlbumListingInvalidator::class . '@handlePhotoMoved');
		Event::listen(PhotoSaved::class, ManagedCacheAlbumListingInvalidator::class . '@handlePhotoSaved');
		Event::listen(PhotoWillBeDeleted::class, ManagedCacheAlbumListingInvalidator::class . '@handlePhotoWillBeDeleted');

		// Managed-cache user-listing invalidation (Feature 053)
		Event::listen(UserGroupMembershipChanged::class, ManagedCacheUserListingInvalidator::class . '@handle');

		// Cached tag/person/smart-album cover purge on access revocation
		Event::listen(UserGroupMembershipChanged::class, PurgeAlbumUserThumbsOnMembershipChange::class . '@handle');

		// Managed-cache photo-listing invalidation
		Event::listen(PhotoSaved::class, ManagedCachePhotoListingInvalidator::class . '@handlePhotoSaved');
		Event::listen(PhotoMoved::class, ManagedCachePhotoListingInvalidator::class . '@handlePhotoMoved');
		Event::listen(PhotoDeleted::class, ManagedCachePhotoListingInvalidator::class . '@handlePhotoDeleted');
		Event::listen(AlbumPhotoSortingChanged::class, ManagedCachePhotoListingInvalidator::class . '@handleAlbumPhotoSortingChanged');
		Event::listen(PhotoBucketsRecomputed::class, ManagedCachePhotoListingInvalidator::class . '@handlePhotoBucketsRecomputed');
		Event::listen(PhotoTagsChanged::class, ManagedCachePhotoListingInvalidator::class . '@handlePhotoTagsChanged');
		Event::listen(PhotoPersonsChanged::class, ManagedCachePhotoListingInvalidator::class . '@handlePhotoPersonsChanged');
		Event::listen(PhotoRatingChanged::class, ManagedCachePhotoListingInvalidator::class . '@handlePhotoRatingChanged');
		Event::listen(PhotoHighlightToggled::class, ManagedCachePhotoListingInvalidator::class . '@handlePhotoHighlightToggled');

		// Managed-cache Map-listing invalidation (Feature 067)
		Event::listen(PhotoSaved::class, ManagedCacheMapListingInvalidator::class . '@handlePhotoSaved');
		Event::listen(PhotoMoved::class, ManagedCacheMapListingInvalidator::class . '@handlePhotoMoved');
		Event::listen(PhotoDeleted::class, ManagedCacheMapListingInvalidator::class . '@handlePhotoDeleted');
		Event::listen(MapListingCacheFlushRequested::class, ManagedCacheMapListingInvalidator::class . '@handleMapListingCacheFlushRequested');

		// Feature 069 - the v3 search cache carries one coarse tag only, so
		// every result-affecting mutation evicts all of it (see the listener's
		// own docblock for why no album-shaped partition would be safe).
		Event::listen(PhotoSaved::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(PhotoMoved::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(PhotoDeleted::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(PhotoTagsChanged::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(PhotoRatingChanged::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(PhotoHighlightToggled::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		// The album half of the same cache (FR-069-23). `/Search/albums`
		// matches on title, description and album tags, and bounds the match
		// by the origin's `_lft`/`_rgt`, so every one of these can change
		// which albums a stored result should have contained.
		Event::listen(AlbumSaved::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(AlbumDeleted::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(AlbumTagsChanged::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(AlbumChildrenChanged::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(AlbumComputedDataUpdated::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(AlbumListingCacheFlushRequested::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		// Security-critical rather than merely stale (FR-069-24): a search
		// entry stores an already-browsability-filtered result, and a cache
		// hit never re-runs that filter, so a revoked grant has to be evicted
		// or it keeps being replayed to the user who just lost access.
		Event::listen(AccessPermissionChanged::class, ManagedCacheSearchListingInvalidator::class . '@handle');
		Event::listen(UserGroupMembershipChanged::class, ManagedCacheSearchListingInvalidator::class . '@handle');
	}
}
