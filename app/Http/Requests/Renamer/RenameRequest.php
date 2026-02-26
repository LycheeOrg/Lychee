<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Renamer;

use App\Contracts\Http\Requests\HasAlbumIds;
use App\Contracts\Http\Requests\HasPhotoIds;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumIdsTrait;
use App\Http\Requests\Traits\HasPhotoIdsTrait;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

/**
 * Request for renaming already existing photos/albums given their Id.
 */
class RenameRequest extends BaseApiRequest implements HasPhotoIds, HasAlbumIds
{
	use HasPhotoIdsTrait;
	use HasAlbumIdsTrait;

	/** @var int[] */
	public array $rule_ids = [];

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIds()]) &&
			Gate::check(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, $this->albumIds()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => ['sometimes', 'array'],
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['string', new RandomIDRule(false)],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => ['sometimes', 'array'],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['string', new RandomIDRule(false)],
			'rule_ids' => ['sometimes', 'array'],
			'rule_ids.*' => ['integer'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE] ?? [];
		$this->album_ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE] ?? [];
		$this->rule_ids = array_map('intval', $values['rule_ids'] ?? []);
	}
}
