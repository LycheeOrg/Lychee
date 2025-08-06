<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Tags;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Models\Tag;
use App\Policies\TagPolicy;
use Illuminate\Support\Facades\Gate;

class DeleteTagRequest extends BaseApiRequest
{
	/** @var int[] */
	public array $tags;

	public function rules(): array
	{
		return [
			RequestAttribute::TAGS_ATTRIBUTE => 'required|array',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'int',
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
		$this->tags = $values[RequestAttribute::TAGS_ATTRIBUTE] ?? [];
	}
}
