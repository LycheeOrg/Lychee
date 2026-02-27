<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

use App\Constants\PhotoAlbum as PA;
use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasAlbumSortingCriterion;
use App\Contracts\Http\Requests\HasCompactBoolean;
use App\Contracts\Http\Requests\HasCopyright;
use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\HasIsPinned;
use App\Contracts\Http\Requests\HasLicense;
use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\HasPhotoLayout;
use App\Contracts\Http\Requests\HasPhotoSortingCriterion;
use App\Contracts\Http\Requests\HasTimelineAlbum;
use App\Contracts\Http\Requests\HasTimelinePhoto;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumSortingCriterionTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasAspectRatioTrait;
use App\Http\Requests\Traits\HasCompactBooleanTrait;
use App\Http\Requests\Traits\HasCopyrightTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasIsPinnedTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Http\Requests\Traits\HasPhotoLayoutTrait;
use App\Http\Requests\Traits\HasPhotoSortingCriterionTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Http\Requests\Traits\HasTimelineAlbumTrait;
use App\Http\Requests\Traits\HasTimelinePhotoTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Rules\CopyrightRule;
use App\Rules\DescriptionRule;
use App\Rules\EnumRequireSupportRule;
use App\Rules\RandomIDRule;
use App\Rules\SlugRule;
use App\Rules\StringRequireSupportRule;
use App\Rules\TitleRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UpdateAlbumRequest extends BaseApiRequest implements HasAlbum, HasTitle, HasDescription, HasLicense, HasPhotoSortingCriterion, HasAlbumSortingCriterion, HasCopyright, HasPhoto, HasCompactBoolean, HasPhotoLayout, HasTimelineAlbum, HasTimelinePhoto, HasIsPinned
{
	use HasAlbumTrait;
	use HasLicenseTrait;
	use HasAspectRatioTrait;
	use HasTitleTrait;
	use HasPhotoTrait;
	use HasCompactBooleanTrait;
	use HasDescriptionTrait;
	use HasPhotoSortingCriterionTrait;
	use HasAlbumSortingCriterionTrait;
	use HasCopyrightTrait;
	use HasPhotoLayoutTrait;
	use HasTimelineAlbumTrait;
	use HasTimelinePhotoTrait;
	use HasIsPinnedTrait;

	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]) &&
			(
				$this->is_compact ||
				$this->photo === null ||
				(DB::table(PA::PHOTO_ALBUM)
					->where(PA::ALBUM_ID, $this->album->id)
					->where(PA::PHOTO_ID, $this->photo->id)
					->count() > 0)
			);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new Enum(LicenseType::class)],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['present', new DescriptionRule()],
			RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE => ['present', 'nullable', new Enum(ColumnSortingPhotoType::class)],
			RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE,
				'nullable',
				new Enum(OrderSortingType::class),
			],
			RequestAttribute::ALBUM_SORTING_COLUMN_ATTRIBUTE => ['present', 'nullable', new Enum(ColumnSortingAlbumType::class)],
			RequestAttribute::ALBUM_SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::ALBUM_SORTING_COLUMN_ATTRIBUTE,
				'nullable',
				new Enum(OrderSortingType::class),
			],
			RequestAttribute::ALBUM_ASPECT_RATIO_ATTRIBUTE => ['present', 'nullable', new Enum(AspectRatioType::class)],
			RequestAttribute::ALBUM_PHOTO_LAYOUT => ['present', 'nullable', new Enum(PhotoLayoutType::class)],
			RequestAttribute::COPYRIGHT_ATTRIBUTE => ['present', 'nullable', new CopyrightRule()],
			RequestAttribute::IS_COMPACT_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::IS_PINNED_ATTRIBUTE => ['present', 'boolean'],
			RequestAttribute::HEADER_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::ALBUM_TIMELINE_ALBUM => ['present', 'nullable', new Enum(TimelineAlbumGranularity::class), new EnumRequireSupportRule(TimelinePhotoGranularity::class, [TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED], $this->verify())],
			RequestAttribute::ALBUM_TIMELINE_PHOTO => ['present', 'nullable', new Enum(TimelinePhotoGranularity::class), new EnumRequireSupportRule(TimelinePhotoGranularity::class, [TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED], $this->verify())],
			RequestAttribute::SLUG_ATTRIBUTE => ['sometimes', 'nullable', new StringRequireSupportRule(null, $this->verify()), new SlugRule($this->input(RequestAttribute::ALBUM_ID_ATTRIBUTE))],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album = $this->album_factory->findBaseAlbumOrFail(
			$values[RequestAttribute::ALBUM_ID_ATTRIBUTE]
		);

		if (!$album instanceof Album) {
			throw ValidationException::withMessages([RequestAttribute::ALBUM_ID_ATTRIBUTE => 'album type not supported.']);
		}

		$this->album = $album;
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE];
		$this->license = LicenseType::tryFrom($values[RequestAttribute::LICENSE_ATTRIBUTE]);

		$photo_column = ColumnSortingPhotoType::tryFrom($values[RequestAttribute::PHOTO_SORTING_COLUMN_ATTRIBUTE]);
		$photo_order = OrderSortingType::tryFrom($values[RequestAttribute::PHOTO_SORTING_ORDER_ATTRIBUTE]);

		$this->photo_sorting_criterion = $photo_column === null ?
			null :
			new PhotoSortingCriterion($photo_column->toColumnSortingType(), $photo_order);

		$album_column = ColumnSortingPhotoType::tryFrom($values[RequestAttribute::ALBUM_SORTING_COLUMN_ATTRIBUTE]);
		$album_order = OrderSortingType::tryFrom($values[RequestAttribute::ALBUM_SORTING_ORDER_ATTRIBUTE]);

		$this->album_sorting_criterion = $album_column === null ?
			null :
			new AlbumSortingCriterion($album_column->toColumnSortingType(), $album_order);

		$this->aspect_ratio = AspectRatioType::tryFrom($values[RequestAttribute::ALBUM_ASPECT_RATIO_ATTRIBUTE]);
		$this->photo_layout = PhotoLayoutType::tryFrom($values[RequestAttribute::ALBUM_PHOTO_LAYOUT]);
		$this->album_timeline = TimelineAlbumGranularity::tryFrom($values[RequestAttribute::ALBUM_TIMELINE_ALBUM]);
		$this->photo_timeline = TimelinePhotoGranularity::tryFrom($values[RequestAttribute::ALBUM_TIMELINE_PHOTO]);

		$this->copyright = $values[RequestAttribute::COPYRIGHT_ATTRIBUTE];

		$this->is_compact = static::toBoolean($values[RequestAttribute::IS_COMPACT_ATTRIBUTE]);
		$this->is_pinned = static::toBoolean($values[RequestAttribute::IS_PINNED_ATTRIBUTE]);

		$slug = $values[RequestAttribute::SLUG_ATTRIBUTE] ?? null;
		$album->slug = ($slug !== '' ? $slug : null);

		if ($this->is_compact) {
			return;
		}

		/** @var string|null $photo_id */
		$photo_id = $values[RequestAttribute::HEADER_ID_ATTRIBUTE];
		$this->photo = $photo_id !== null ? Photo::query()->findOrFail($photo_id) : null;
	}
}
