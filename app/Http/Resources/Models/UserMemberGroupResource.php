<?php

namespace App\Http\Resources\Model;

use App\Enum\UserGroupRole;
use Spatie\LaravelData\Data;

class UserMemberGroupResource extends Data
{
	public function __construct(
		public int $id,
		public string $username,
		public UserGroupRole $role,
	) {
	}
}
