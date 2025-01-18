<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasParentAlbum;
use App\Legacy\V1\Contracts\Http\Requests\HasTitle;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasParentAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasTitleTrait;
use App\Legacy\V1\RuleSets\Album\AddAlbumRuleSet;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

final class AddAlbumRequest extends BaseApiRequest implements HasTitle, HasParentAlbum
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
		return AddAlbumRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string|null */
		$parentAlbumID = $values[RequestAttribute::PARENT_ID_ATTRIBUTE];
		$this->parentAlbum = $parentAlbumID === null ?
			null :
			Album::query()->findOrFail($parentAlbumID);
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
	}
}
