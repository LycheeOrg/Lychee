<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Runner\BeforeFirstTestHook;

class Boot implements BeforeFirstTestHook
{
	use CreatesApplication;

	public function executeBeforeFirstTest(): void
	{
		$this->createApplication();
		/** @var User $admin */
		$admin = User::find(1);
		if ($admin === null) {
			$admin = new User();
			$admin->incrementing = false;
			$admin->id = 1;
			$admin->may_upload = true;
			$admin->may_edit_own_settings = true;
			$admin->may_administrate = true;
			$admin->username = 'admin';
			$admin->password = Hash::make('password');
			$admin->save();
		} elseif (!$admin->may_administrate) {
			$admin->may_administrate = true;
			$admin->save();
		}
	}
}