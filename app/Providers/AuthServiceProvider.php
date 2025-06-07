<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Providers;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\OauthProvidersType;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\LiveMetrics;
use App\Models\Photo;
use App\Models\User;
use App\Models\UserGroup;
use App\Policies\AlbumPolicy;
use App\Policies\MetricsPolicy;
use App\Policies\PhotoPolicy;
use App\Policies\SettingsPolicy;
use App\Policies\UserGroupPolicy;
use App\Policies\UserPolicy;
use App\Services\Auth\SessionOrTokenGuard;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array<class-string,class-string>
	 */
	protected $policies = [
		User::class => UserPolicy::class,

		Photo::class => PhotoPolicy::class,

		// This ensures that all the kinds of albums are covered in the Gate mapping.
		BaseSmartAlbum::class => AlbumPolicy::class,
		BaseAlbum::class => AlbumPolicy::class,
		Album::class => AlbumPolicy::class,
		AbstractAlbum::class => AlbumPolicy::class,

		Configs::class => SettingsPolicy::class,

		LiveMetrics::class => MetricsPolicy::class,

		UserGroup::class => UserGroupPolicy::class,
	];

	/**
	 * Register any authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->registerPolicies();
		// The identifier "session-or-token" is used in config/auth.php.
		Auth::extend('session-or-token', function (Application $app, string $name, array $config) {
			return SessionOrTokenGuard::createGuard($app, $name, $config);
		});
	}

	/**
	 * Return the available OAuth providers.
	 * Those are the ones where the service.<provider_name>.client_id is set in .env.
	 *
	 * @return array
	 *
	 * @throws BindingResolutionException
	 */
	public static function getAvailableOauthProviders(): array
	{
		$oauth_available = [];

		foreach (OauthProvidersType::cases() as $oauth_provider) {
			$client_id = config('services.' . $oauth_provider->value . '.client_id');
			if ($client_id === null || $client_id === '') {
				continue;
			}

			$oauth_available[] = $oauth_provider;
		}

		return $oauth_available;
	}

	public static function isOauthEnabled(): bool
	{
		return count(self::getAvailableOauthProviders()) > 0;
	}

	public static function isWebAuthnEnabled(): bool
	{
		return config('features.disable-webauthn', false) === false;
	}

	public static function isBasicAuthEnabled(): bool
	{
		return config('features.disable-basic-auth', false) === false;
	}
}
