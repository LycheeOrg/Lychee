<?php

declare(strict_types=1);

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasTags;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Http\RuleSets\Album\AddTagAlbumRuleSet;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

class AddTagAlbumRequest extends BaseApiRequest implements HasTitle, HasTags
{
	use HasTitleTrait;
	use HasTagsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		// Sic!
		// Tag albums can only be created below the root album which has the
		// ID `null`.
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return AddTagAlbumRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
		$this->tags = $values[RequestAttribute::TAGS_ATTRIBUTE];
	}
}
