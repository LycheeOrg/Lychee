<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Tags;

use App\Contracts\Http\Requests\HasTag;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasTagTrait;
use App\Models\Tag;
use App\Policies\TagPolicy;
use Illuminate\Support\Facades\Gate;

class MergeTagRequest extends BaseApiRequest implements HasTag
{
	use HasTagTrait;

	protected Tag $destination_tag;

	public function rules(): array
	{
		return [
			RequestAttribute::TAG_ID => 'required|int',
			'destination_id' => 'required|int|different:' . RequestAttribute::TAG_ID,
		];
	}

	public function authorize(): bool
	{
		return Gate::check(TagPolicy::CAN_EDIT, [Tag::class]);
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->tag = Tag::query()->findOrFail($values[RequestAttribute::TAG_ID]);
		$this->destination_tag = Tag::query()->findOrFail($values['destination_id']);
	}

	/**
	 * Get the destination tag to merge into.
	 *
	 * @return Tag
	 */
	public function destinationTag(): Tag
	{
		return $this->destination_tag;
	}
}
