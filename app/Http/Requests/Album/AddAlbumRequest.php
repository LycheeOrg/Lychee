<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasParentAlbum;
use App\Http\Requests\Contracts\HasTitle;
use App\Http\Requests\Traits\HasParentAlbumTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Models\Album;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;
use Illuminate\Support\Facades\Gate;

class AddAlbumRequest extends BaseApiRequest implements HasTitle, HasParentAlbum
{
	use HasTitleTrait;
	use HasParentAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, $this->parentAlbum ?? Album::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasParentAlbum::PARENT_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			HasTitle::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$parentAlbumID = $values[HasParentAlbum::PARENT_ID_ATTRIBUTE];
		$this->parentAlbum = $parentAlbumID === null ?
			null :
			Album::query()->findOrFail(
				$values[HasParentAlbum::PARENT_ID_ATTRIBUTE]
			);
		$this->title = $values[HasTitle::TITLE_ATTRIBUTE];
	}
}
