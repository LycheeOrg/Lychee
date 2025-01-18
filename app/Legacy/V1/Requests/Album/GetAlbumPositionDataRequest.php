<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasAbstractAlbum;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasAbstractAlbumTrait;
use App\Policies\AlbumPolicy;
use App\Rules\AlbumIDRule;
use Illuminate\Support\Facades\Gate;

final class GetAlbumPositionDataRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	public const INCLUDE_SUB_ALBUMS_ATTRIBUTE = 'includeSubAlbums';

	protected bool $includeSubAlbums = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			self::INCLUDE_SUB_ALBUMS_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// Avoid loading all photos and sub-albums of an album, because
		// \App\Actions\Album\PositionData::get is only interested in a
		// particular subset of photos.
		$this->album = $this->albumFactory->findAbstractAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE], false);
		$this->includeSubAlbums = static::toBoolean($values[self::INCLUDE_SUB_ALBUMS_ATTRIBUTE]);
	}

	public function includeSubAlbums(): bool
	{
		return $this->includeSubAlbums;
	}
}
