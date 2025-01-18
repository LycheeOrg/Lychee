<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasAlbums;
use App\Legacy\V1\Contracts\Http\Requests\HasTitle;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasAlbumsTrait;
use App\Legacy\V1\Requests\Traits\HasTitleTrait;
use App\Legacy\V1\RuleSets\Album\SetAlbumsTitleRuleSet;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * We cannot use <BaseAlbum>, even though it is indeed true.
 * This violate LSP and contra variance.
 *
 * SetAlbumsTitleRuleSet ensure that we are actually dealing with BaseAlbum
 *
 * @implements HasAlbums<\App\Models\Album|\App\Models\TagAlbum>
 */
final class SetAlbumsTitleRequest extends BaseApiRequest implements HasTitle, HasAlbums
{
	use HasTitleTrait;
	/** @use HasAlbumsTrait<\App\Models\Album|\App\Models\TagAlbum> */
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var AbstractAlbum $album */
		foreach ($this->albums as $album) {
			if (!Gate::check(AlbumPolicy::CAN_EDIT, $album)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetAlbumsTitleRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albums = $this->albumFactory->findBaseAlbumsOrFail(
			$values[RequestAttribute::ALBUM_IDS_ATTRIBUTE], false
		);
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
	}
}
