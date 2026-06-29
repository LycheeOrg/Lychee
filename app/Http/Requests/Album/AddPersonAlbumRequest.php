<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasIsAnd;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasIsAndTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Policies\AlbumPolicy;
use App\Rules\TitleRule;
use Illuminate\Support\Facades\Gate;

class AddPersonAlbumRequest extends BaseApiRequest implements HasTitle, HasIsAnd
{
	use HasTitleTrait;
	use HasIsAndTrait;

	/** @var string[] */
	protected array $person_ids = [];

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null]) &&
			config('features.v8') === true &&
			request()->configs()->getValueAsBool('ai_vision_face_enabled');
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			'persons' => 'required|array|min:1',
			'persons.*' => 'required|string|exists:persons,id',
			RequestAttribute::IS_AND_ATTRIBUTE => ['required', 'boolean'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
		$this->person_ids = $values['persons'];
		$this->is_and = static::toBoolean($values[RequestAttribute::IS_AND_ATTRIBUTE]);
	}

	/**
	 * @return string[]
	 */
	public function personIds(): array
	{
		return $this->person_ids;
	}
}
