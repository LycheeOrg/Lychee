<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\Policies\UserPolicy;
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
		// User::class => UserPolicy::class,
		// 'App\Model' => 'App\Policies\ModelPolicy',
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

		Gate::define('can-upload', [UserPolicy::class, 'upload']);
		Gate::define('editById-albums', [AlbumPolicy::class, 'editById']);
		Gate::define('editById-photos', [PhotoPolicy::class, 'editById']);
	}
}
