<?php

namespace App\Actions\WebAuth;

use App\Facades\AccessControl;

class Delete
{
	public function do($ids): void
	{
		$user = AccessControl::user();
		$user->removeCredential($ids);
	}
}
