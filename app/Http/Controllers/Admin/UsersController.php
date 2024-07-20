<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Users\CountUserRequest;
use App\Models\User;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for the config.
 */
class UsersController extends Controller
{
	public function count(CountUserRequest $_request): int
	{
		return User::count();
	}
}