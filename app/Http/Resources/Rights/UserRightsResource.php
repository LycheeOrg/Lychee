<?php

namespace App\Http\Resources\Rights;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UserRightsResource extends Data
{
	public bool $can_edit;

	public function __construct()
	{
		$this->can_edit = Gate::check(UserPolicy::CAN_EDIT, [User::class]);
	}
}
