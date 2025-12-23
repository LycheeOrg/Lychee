<?php

namespace App\Providers;

use App\Http\WriteOnceParameterBag;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class WriteOnceRequestProvider extends ServiceProvider
{
	public function register()
	{
	}

	public function boot()
	{
		// Swap Request's attributes for every incoming request
		Request::macro('bootWriteOnceAttributes', function (): void {
			$this->attributes = new WriteOnceParameterBag($this->attributes->all());
		});

		// Automatically boot for all requests
		$this->app->resolving(Request::class, function (Request $request): void {
			$request->bootWriteOnceAttributes();
		});
	}
}
