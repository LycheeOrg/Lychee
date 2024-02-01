<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Amazon\AmazonExtendSocialite;
use SocialiteProviders\Apple\AppleExtendSocialite;
use SocialiteProviders\Facebook\FacebookExtendSocialite;
use SocialiteProviders\GitHub\GitHubExtendSocialite;
use SocialiteProviders\Google\GoogleExtendSocialite;
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
			FacebookExtendSocialite::class . '@handle',
			GitHubExtendSocialite::class . '@handle',
			GoogleExtendSocialite::class . '@handle',
			// Mastodon is provided directly.
			MicrosoftExtendSocialite::class . '@handle',
			NextcloudExtendSocialite::class . '@handle',
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
