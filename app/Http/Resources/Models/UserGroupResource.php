<?php

namespace App\Http\Resources\Model;

use App\Models\UserGroup;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class UserGroupResource extends Data
{
	public int $id;
	public string $name;
	public string $description;
	/** @var Collection<int,UserMemberGroupResource> */
	public Collection $members;

	public function __construct(UserGroup $group)
	{
		$this->id = $group->id;
		$this->name = $group->name;
		$this->description = $group->description;
		$this->members = $group->users->map(function ($user) {
			return new UserMemberGroupResource(
				id: $user->id,
				username: $user->username,
				// @phpstan-ignore-next-line
				role: $user->pivot->role
			);
		});
	}
}
