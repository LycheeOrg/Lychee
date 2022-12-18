<?php

namespace App\Http\Requests\Photo;

use App\Actions\Photo\Archive;
use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\HasSizeVariant;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\Requests\Traits\HasSizeVariantTrait;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Rules\RandomIDListRule;
use App\Rules\SizeVariantRule;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Gate;

class ArchivePhotosRequest extends BaseApiRequest implements HasPhotos, HasSizeVariant
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
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => ['required', new RandomIDListRule()],
			RequestAttribute::SIZE_VARIANT_ATTRIBUTE => ['required', new SizeVariantRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->sizeVariant = $values[RequestAttribute::SIZE_VARIANT_ATTRIBUTE];

		$photoQuery = Photo::with(['album']);
		// The condition is required, because Lychee also supports to archive
		// the "live video" as a size variant which is not a proper size variant
		if (array_key_exists($this->sizeVariant, Archive::VARIANT2VARIANT)) {
			// If a proper size variant is requested, eagerly load the size
			// variants but only the requested type due to efficiency reasons
			$photoQuery = $photoQuery->with([
				'size_variants' => function (HasMany $r) {
					// The ridiculous mapping `VARIANT2VARIANT` is only
					// necessary, because the size variant with the largest
					// dimensions is called `FULL` by the front-end in the
					// context of archiving, but `ORIGINAL` everywhere else.
					// This should be made consistent.
					// Although, `ORIGINAL` is used more prominently, `FULL`
					// is probably the better wording.
					// TODO: Fix this and make it consistent.
					$r->where('type', '=', Archive::VARIANT2VARIANT[$this->sizeVariant]);
				},
			]);
		}
		// `findOrFail` returns the union `Photo|Collection<Photo>`
		// which is not assignable to `Collection<Photo>`; but as we query
		// with an array of IDs we never get a single entity (even if the
		// array only contains a single ID).
		// @phpstan-ignore-next-line
		$this->photos = $photoQuery->findOrFail(
			explode(',', $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE])
		);
	}
}
