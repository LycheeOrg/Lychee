<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\UserManagementResource;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UserManagementCollectionResource extends Data
{
	/** @var UserManagementResource[] */
	public array $users;

	/**
	 * @param Collection<int,User> $users
	 *
	 * @return void
	 */
	public function __construct(Collection $users)
	{
		$this->users = $users->map(fn (User $user) => new UserManagementResource($user))->all();
	}
}