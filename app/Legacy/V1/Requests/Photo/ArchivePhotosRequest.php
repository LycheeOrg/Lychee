<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Photo;

use App\Enum\DownloadVariantType;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasPhotos;
use App\Legacy\V1\Contracts\Http\Requests\HasSizeVariant;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasPhotosTrait;
use App\Legacy\V1\Requests\Traits\HasSizeVariantTrait;
use App\Legacy\V1\RuleSets\Photo\ArchivePhotosRuleSet;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

final class ArchivePhotosRequest extends BaseApiRequest implements HasPhotos, HasSizeVariant
{
	use HasPhotosTrait;
	use HasSizeVariantTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		/** @var Photo $photo */
		foreach ($this->photos as $photo) {
			if (!Gate::check(PhotoPolicy::CAN_DOWNLOAD, $photo)) {
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
		return ArchivePhotosRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->sizeVariant = DownloadVariantType::from($values[RequestAttribute::SIZE_VARIANT_ATTRIBUTE]);

		$photoQuery = Photo::query()->with(['album']);
		// The condition is required, because Lychee also supports to archive
		// the "live video" as a size variant which is not a proper size variant
		$variant = $this->sizeVariant->getSizeVariantType();
		if ($variant !== null) { // NOT LIVE PHOTO
			// If a proper size variant is requested, eagerly load the size
			// variants but only the requested type due to efficiency reasons
			$photoQuery = $photoQuery->with([
				'size_variants' => fn ($r) => $r->where('type', '=', $variant),
			]);
		}
		// `findOrFail` returns the union `Photo|Collection<int,Photo>`
		// which is not assignable to `Collection<int,Photo>`; but as we query
		// with an array of IDs we never get a single entity (even if the
		// array only contains a single ID).
		$this->photos = $photoQuery->findOrFail(
			explode(',', $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE])
		);
	}
}
