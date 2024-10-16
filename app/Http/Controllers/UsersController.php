<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\ListUsersRequest;
use App\Http\Requests\Users\UsersRequest;
use App\Http\Resources\Models\LightUserResource;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Controller responsible for the config.
 */
class UsersController extends Controller
{
	public function count(UsersRequest $_request): int
	{
		return User::count();
	}

	/**
	 * Get the list of users for sharing & transfer purposes.
	 *
	 * @param ListUsersRequest $_request
	 *
	 * @return Collection<array-key, LightUserResource>
	 */
	public function list(ListUsersRequest $_request): Collection
	{
		return LightUserResource::collect(User::where('id', '!=', Auth::id())->get());
	}
}