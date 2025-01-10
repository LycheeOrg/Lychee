<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasBaseAlbum;
use App\Contracts\Http\Requests\HasUser;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasUserTrait;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\Gate;

class TransferAlbumRequest extends BaseApiRequest implements HasBaseAlbum, HasUser
{
	use HasBaseAlbumTrait;
	use HasUserTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_TRANSFER, [AbstractAlbum::class, $this->album()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			RequestAttribute::USER_ID_ATTRIBUTE => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var int $user_id */
		$user_id = $values[RequestAttribute::USER_ID_ATTRIBUTE];
		$this->user2 = User::findOrFail($user_id);
		// We don't need the full albums, just the IDs, so we don't load the
		// models for efficiency reasons.
		// Instead, we use mass deletion via low-level SQL queries later.
		$this->album = $this->albumFactory->findBaseAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
	}
}
