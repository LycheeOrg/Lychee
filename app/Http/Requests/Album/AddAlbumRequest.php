<?php

declare(strict_types=1);

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasParentAlbum;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasParentAlbumTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Http\RuleSets\Album\AddAlbumRuleSet;
use App\Models\Album;
use App\Policies\AlbumPolicy;
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
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->parentAlbum]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return AddAlbumRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string|null */
		$parentAlbumID = $values[RequestAttribute::PARENT_ID_ATTRIBUTE];
		$this->parentAlbum = $parentAlbumID === null ?
			null :
			Album::query()->findOrFail($parentAlbumID);
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
	}
}
