<?php

namespace App\Actions\WebAuth;

use Illuminate\Support\Facades\Auth;

class Delete
{
	public function do(string|array $ids): void
	{
		Auth::authenticate()->removeCredential($ids);
	}
}
