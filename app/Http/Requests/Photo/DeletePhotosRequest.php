<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Constants\PhotoAlbum as PA;
use App\Constants\QueryLimits;
use App\Contracts\Http\Requests\HasFromId;
use App\Contracts\Http\Requests\HasPhotoIds;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\SmartAlbumType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasFromIdTrait;
use App\Http\Requests\Traits\HasPhotoIdsTrait;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Rules\AlbumIDRule;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeletePhotosRequest extends BaseApiRequest implements HasPhotoIds, HasFromId
{
	use HasPhotoIdsTrait;
	use HasFromIdTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_DELETE_BY_ID, [Photo::class, $this->photoIds()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::FROM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	/**
	 * {@inheritDoc}
	 *
	 * After the permission check, every selected photo must be in `from_id`,
	 * or, for Unsorted, in no album (Feature 119, FR-119-09). Otherwise the
	 * delete would silently keep it: refused with 422, nothing deleted.
	 */
	public function validateResolved(): void
	{
		parent::validateResolved();

		$outside = $this->photosOutside($this->photoIds(), $this->from_id);
		if ($outside !== []) {
			throw ValidationException::withMessages([RequestAttribute::PHOTO_IDS_ATTRIBUTE => [self::outsideMessage($outside)]]);
		}
	}

	/**
	 * The selected photos that are not linked to `$from_id`; for Unsorted,
	 * those linked to any album.
	 *
	 * @param string[] $photo_ids
	 *
	 * @return string[]
	 */
	private function photosOutside(array $photo_ids, string $from_id): array
	{
		$outside = [];
		foreach (array_chunk(array_values(array_unique($photo_ids)), QueryLimits::ID_CHUNK_SIZE) as $chunk) {
			$links = DB::table(PA::PHOTO_ALBUM)->whereIn(PA::PHOTO_ID, $chunk);
			if ($from_id === SmartAlbumType::UNSORTED->value) {
				$outside = array_merge($outside, $links->distinct()->pluck(PA::PHOTO_ID)->all());
			} else {
				$inside = $links->where(PA::ALBUM_ID, '=', $from_id)->pluck(PA::PHOTO_ID)->all();
				$outside = array_merge($outside, array_values(array_diff($chunk, $inside)));
			}
		}

		return $outside;
	}

	/**
	 * @param string[] $outside
	 */
	private static function outsideMessage(array $outside): string
	{
		$shown = array_slice($outside, 0, 10);
		$more = count($outside) - count($shown);

		return 'These photos are not in the album they are deleted from: ' . implode(', ', $shown) . ($more > 0 ? ' and ' . $more . ' more.' : '.');
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		// As we are going to delete the photos anyway, we don't load the
		// models for efficiency reasons.
		// Instead, we use mass deletion via low-level SQL queries later.
		$this->photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->from_id = $values[RequestAttribute::FROM_ID_ATTRIBUTE];
	}
}
