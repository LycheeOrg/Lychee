<?php

namespace App\ControllerFunctions\WebAuth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Delete
{
	public function do($ids)
	{
		/**
		 * @var User
		 */
		$user = Auth::user();
		$user->removeCredential($ids);

		return 'true';
	}
}
