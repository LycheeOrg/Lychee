<?php

namespace App\Actions\WebAuth;

use AccessControl;

class Delete
{
	public function do($ids)
	{
		$user = AccessControl::user();
		$user->removeCredential($ids);

		return 'true';
	}
}
