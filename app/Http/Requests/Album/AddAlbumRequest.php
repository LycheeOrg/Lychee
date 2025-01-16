<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasParentAlbum;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasParentAlbumTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;
use Illuminate\Support\Facades\Gate;

class AddAlbumRequest extends BaseApiRequest implements HasTitle, HasParentAlbum
{
	use HasTitleTrait;
	use HasParentAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->parentAlbum]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PARENT_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string|null */
		$parentAlbumID = $values[RequestAttribute::PARENT_ID_ATTRIBUTE];
		$this->parent_album = $parentAlbumID === null ?
			null :
			Album::query()->findOrFail($parentAlbumID);
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
	}
}
