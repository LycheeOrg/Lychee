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
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Request for previewing renamer rule application on photos/albums.
 */
class PreviewRenameRequest extends BaseApiRequest implements HasPhotoIds, HasAlbumIds
{
	use HasPhotoIdsTrait;
	use HasAlbumIdsTrait;

	public string $album_id = '';
	public string $target = 'photos';
	public string $scope = 'current';

	/** @var int[] */
	public array $rule_ids = [];

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// If explicit photo/album IDs are provided (context menu path), authorize those
		if (count($this->photoIds()) > 0) {
			return Gate::check(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIds()]);
		}

		if (count($this->albumIds()) > 0) {
			return Gate::check(AlbumPolicy::CAN_EDIT_ID, [AbstractAlbum::class, $this->albumIds()]);
		}

		// Otherwise, authorize via album_id (hero button path)
		if ($this->album_id !== '') {
			return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, Album::query()->findOrFail($this->album_id)]);
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['sometimes', 'string', new RandomIDRule(false)],
			'target' => ['required', Rule::in(['photos', 'albums'])],
			'scope' => ['required', Rule::in(['current', 'descendants'])],
			'rule_ids' => ['required', 'array', 'min:1'],
			'rule_ids.*' => ['integer'],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => ['sometimes', 'array'],
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['string', new RandomIDRule(false)],
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => ['sometimes', 'array'],
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['string', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album_id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE] ?? '';
		$this->target = $values['target'];
		$this->scope = $values['scope'];
		$this->rule_ids = array_map('intval', $values['rule_ids']);
		$this->photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE] ?? [];
		$this->album_ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE] ?? [];
	}
}
