<?php

namespace App\Actions\WebAuth;

use App\Facades\AccessControl;

class Delete
{
	public function do(string|array $ids): void
	{
		$user = AccessControl::user();
		$user->removeCredential($ids);
	}
}
