<?php

namespace App\Actions\WebAuth;

use App\Auth\Authorization;

class Delete
{
	public function do(string|array $ids): void
	{
		Authorization::user()->removeCredential($ids);
	}
}
