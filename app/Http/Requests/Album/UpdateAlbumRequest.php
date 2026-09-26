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
use App\Contracts\Http\Requests\HasDateScrubber;
use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\HasIsPinned;
use App\Contracts\Http\Requests\HasLicense;
use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\HasPhotoLayout;
use App\Contracts\Http\Requests\HasPhotoSortingCriterion;
use App\Contracts\Http\Requests\HasPublishedAt;
use App\Contracts\Http\Requests\HasTags;
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
use App\Http\Requests\Traits\HasDateScrubberTrait;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasIsPinnedTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Http\Requests\Traits\HasPhotoLayoutTrait;
use App\Http\Requests\Traits\HasPhotoSortingCriterionTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Http\Requests\Traits\HasPublishedAtTrait;
use App\Http\Requests\Traits\HasTagsTrait;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UpdateAlbumRequest extends BaseApiRequest implements HasAlbum, HasTitle, HasDescription, HasLicense, HasPhotoSortingCriterion, HasAlbumSortingCriterion, HasCopyright, HasPhoto, HasCompactBoolean, HasPhotoLayout, HasTimelineAlbum, HasTimelinePhoto, HasDateScrubber, HasIsPinned, HasTags, HasPublishedAt
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
	use HasDateScrubberTrait;
	use HasIsPinnedTrait;
	use HasTagsTrait;
	use HasPublishedAtTrait;

	private bool $tags_provided = false;
	private ?Photo $cover_photo = null;

	/**
	 * The photo to use as the album's manually-selected cover, or null to
	 * fall back to auto-selection.
	 */
	public function coverPhoto(): ?Photo
	{
		return $this->cover_photo;
	}

	/**
	 * Whether the `tags` key was present in the request payload at all.
	 *
	 * The legacy v7 frontend never sends this key; `PATCH /Album` must leave
	 * existing album tags untouched in that case rather than clearing them.
	 */
	public function tagsProvided(): bool
	{
		return $this->tags_provided;
	}

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
			) &&
			(
				$this->cover_photo === null ||
				DB::table(PA::PHOTO_ALBUM)
					->join('albums', 'albums.id', '=', PA::ALBUM_ID)
					->where(PA::PHOTO_ID, $this->cover_photo->id)
					->where('albums._lft', '>=', $this->album->_lft)
					->where('albums._rgt', '<=', $this->album->_rgt)
					->exists()
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
			// Optional (not `present`): the legacy v7 frontend does not know about
			// album tags and never sends this key. Omitting it must leave existing
			// album tags untouched (see UpdateAlbumRequest::tagsProvided()).
			RequestAttribute::TAGS_ATTRIBUTE => 'sometimes|array',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
			RequestAttribute::IS_COMPACT_ATTRIBUTE => ['required', 'boolean'],
			RequestAttribute::IS_PINNED_ATTRIBUTE => ['present', 'boolean'],
			RequestAttribute::HEADER_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::COVER_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::ALBUM_TIMELINE_ALBUM => ['present', 'nullable', new Enum(TimelineAlbumGranularity::class), new EnumRequireSupportRule(TimelinePhotoGranularity::class, [TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED], $this->verify())],
			RequestAttribute::ALBUM_TIMELINE_PHOTO => ['present', 'nullable', new Enum(TimelinePhotoGranularity::class), new EnumRequireSupportRule(TimelinePhotoGranularity::class, [TimelinePhotoGranularity::DEFAULT, TimelinePhotoGranularity::DISABLED], $this->verify())],
			RequestAttribute::ALBUM_DATE_SCRUBBER => ['sometimes', 'nullable', 'boolean'],
			RequestAttribute::SLUG_ATTRIBUTE => ['sometimes', 'nullable', new StringRequireSupportRule(null, $this->verify()), new SlugRule($this->input(RequestAttribute::ALBUM_ID_ATTRIBUTE))],
			// Feature 068 (FR-068-13): `sometimes`, not `present` - confirmed via
			// the existing AlbumUpdateTest/AlbumUpdateFocusTest regression suite
			// (11 failures on a `present` attempt): every *existing* caller of
			// this endpoint (today's v8 frontend included, until T-068-24 lands)
			// omits this brand-new key entirely, exactly the same reason
			// `slug`/`tags` above are `sometimes` rather than `present` - a
			// field no caller has ever been asked to send cannot retroactively
			// become mandatory without breaking every caller that hasn't been
			// updated yet.
			RequestAttribute::PUBLISHED_AT_ATTRIBUTE => ['sometimes', 'nullable', 'date'],
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
		$this->is_date_scrubber_enabled_provided = array_key_exists(RequestAttribute::ALBUM_DATE_SCRUBBER, $values);
		$date_scrubber = $values[RequestAttribute::ALBUM_DATE_SCRUBBER] ?? null;
		$this->is_date_scrubber_enabled = $date_scrubber === null ? null : static::toBoolean($date_scrubber);

		$this->copyright = $values[RequestAttribute::COPYRIGHT_ATTRIBUTE];
		$this->tags_provided = array_key_exists(RequestAttribute::TAGS_ATTRIBUTE, $values);
		$this->tags = $values[RequestAttribute::TAGS_ATTRIBUTE] ?? [];

		$this->is_compact = static::toBoolean($values[RequestAttribute::IS_COMPACT_ATTRIBUTE]);
		$this->is_pinned = static::toBoolean($values[RequestAttribute::IS_PINNED_ATTRIBUTE]);

		$slug = $values[RequestAttribute::SLUG_ATTRIBUTE] ?? null;
		$album->slug = ($slug !== '' ? $slug : null);

		$this->published_at_provided = array_key_exists(RequestAttribute::PUBLISHED_AT_ATTRIBUTE, $values);
		/** @var string|null $published_at */
		$published_at = $values[RequestAttribute::PUBLISHED_AT_ATTRIBUTE] ?? null;
		$this->published_at = $published_at !== null ? Carbon::parse($published_at) : null;

		/** @var string|null $cover_id */
		$cover_id = $values[RequestAttribute::COVER_ID_ATTRIBUTE];
		$this->cover_photo = $cover_id !== null ? Photo::query()->findOrFail($cover_id) : null;

		if ($this->is_compact) {
			return;
		}

		/** @var string|null $photo_id */
		$photo_id = $values[RequestAttribute::HEADER_ID_ATTRIBUTE];
		$this->photo = $photo_id !== null ? Photo::query()->findOrFail($photo_id) : null;
	}
}
