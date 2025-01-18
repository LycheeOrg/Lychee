<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasTags;
use App\Legacy\V1\Contracts\Http\Requests\HasTitle;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasTagsTrait;
use App\Legacy\V1\Requests\Traits\HasTitleTrait;
use App\Legacy\V1\RuleSets\Album\AddTagAlbumRuleSet;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

final class AddTagAlbumRequest extends BaseApiRequest implements HasTitle, HasTags
{
	use HasTitleTrait;
	use HasTagsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// Sic!
		// Tag albums can only be created below the root album which has the
		// ID `null`.
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return AddTagAlbumRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
		$this->tags = $values[RequestAttribute::TAGS_ATTRIBUTE];
	}
}
