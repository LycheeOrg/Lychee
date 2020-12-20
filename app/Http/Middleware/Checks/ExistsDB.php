<?php

namespace App\Http\Middleware\Checks;

use App\Contracts\MiddlewareCheck;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class ExistsDB implements MiddlewareCheck
{
	public function assert(): bool
	{
		try {
			if (!Schema::hasTable('configs')) {
				return false;
			}
		} catch (QueryException $e) {
			return false;
		}

		return true;
	}
}
