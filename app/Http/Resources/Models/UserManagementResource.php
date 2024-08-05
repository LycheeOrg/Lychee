<?php

namespace App\Http\Resources\Models;

use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UserManagementResource extends Data
{
	public int $id;
	public string $username;
	public bool $may_administrate;
	public bool $may_upload;
	public bool $may_edit_own_settings;

	public function __construct(User $user)
	{
		$this->id = $user->id;
		$this->username = $user->username;
		$this->may_administrate = $user->may_administrate;
		$this->may_upload = $user->may_upload;
		$this->may_edit_own_settings = $user->may_edit_own_settings;
	}

	public static function fromModel(User $user): UserManagementResource
	{
		return new self($user);
	}
}
