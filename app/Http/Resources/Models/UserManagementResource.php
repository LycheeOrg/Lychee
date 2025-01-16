<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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

	public ?int $quota_kb = null;
	public ?string $description = null;
	public ?string $note = null;
	public ?int $space = null;

	/**
	 * @param User                   $user
	 * @param array{id:int,size:int} $space
	 * @param bool                   $is_se
	 *
	 * @return void
	 */
	public function __construct(User $user, array $space, bool $is_se)
	{
		$this->id = $user->id;
		$this->username = $user->username;
		$this->may_administrate = $user->may_administrate;
		$this->may_upload = $user->may_upload || $user->may_administrate;
		$this->may_edit_own_settings = $user->may_edit_own_settings || $user->may_administrate;
		if ($is_se) {
			$this->quota_kb = $user->quota_kb;
			$this->description = $user->description;
			$this->note = $user->note;
			$this->space = $space['size'];
		}
		if ($user->id !== $space['id']) {
			throw new \RuntimeException('User and space id do not match');
		}
	}
}
