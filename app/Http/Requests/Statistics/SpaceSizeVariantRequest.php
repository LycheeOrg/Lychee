<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Statistics;

use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\HasOwnerId;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\Requests\Traits\HasOwnerIdTrait;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\UserPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SpaceSizeVariantRequest extends BaseApiRequest implements HasAbstractAlbum, HasOwnerId
{
	use HasAbstractAlbumTrait;
	use HasOwnerIdTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if ($this->album === null) {
			return Gate::check(UserPolicy::CAN_EDIT, [User::class]);
		}

		return Auth::check() && Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['sometimes', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string|null */
		$albumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE] ?? null;
		$this->album = $this->albumFactory->findNullalbleAbstractAlbumOrFail($albumID);

		// Filter only to user if user is not admin
		if (Auth::check() && Auth::user()?->may_administrate !== true) {
			$this->owner_id = intval(Auth::id());
		}
	}
}
