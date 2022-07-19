<?php

namespace App\Providers;

use App\Models\User;
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

		Gate::define('admin', function (?User $user) {
			return optional($user)->id === 0;
		});

		Gate::define('can-upload', function (?User $user) {
			return optional($user)->may_upload === true;
		});
	}
}
