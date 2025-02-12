<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
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
	}
}
