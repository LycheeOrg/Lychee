<?php

namespace App\Providers;

use App\Contracts\AbstractAlbum;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use App\Models\Extensions\BaseAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\Policies\UserPolicy;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array<string, string>
	 */
	protected $policies = [
		User::class => UserPolicy::class,
		Photo::class => PhotoPolicy::class,
		BaseSmartAlbum::class => AlbumPolicy::class,
		BaseAlbum::class => AlbumPolicy::class,
		BaseAlbumImpl::class => AlbumPolicy::class,
		Album::class => AlbumPolicy::class,
		AbstractAlbum::class => AlbumPolicy::class,
	];

	/**
	 * Register any authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->registerPolicies();

		Gate::define('admin', function (User $user) {
			return $user->isAdmin();
		});
	}
}
