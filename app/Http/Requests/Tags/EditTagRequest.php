<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Tags;

use App\Contracts\Http\Requests\HasName;
use App\Contracts\Http\Requests\HasTag;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasNameTrait;
use App\Http\Requests\Traits\HasTagTrait;
use App\Models\Tag;
use App\Policies\TagPolicy;
use Illuminate\Support\Facades\Gate;

class EditTagRequest extends BaseApiRequest implements HasTag, HasName
{
	use HasTagTrait;
	use HasNameTrait;

	public function rules(): array
	{
		return [
			RequestAttribute::TAG_ID => 'required|int',
			RequestAttribute::NAME_ATTRIBUTE => 'required|string|min:3|max:255',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(TagPolicy::CAN_EDIT, [Tag::class]);
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->tag = Tag::query()->findOrFail($values[RequestAttribute::TAG_ID]);
		$this->name = trim($values[RequestAttribute::NAME_ATTRIBUTE]);
	}
}
