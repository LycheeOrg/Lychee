<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\DateOrderingType;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Http\Resources\Models\Utils\TimelineData;
use App\Http\Resources\Rights\AlbumRightsResource;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\Models\TagAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ThumbAlbumResource extends Data
{
	public string $id;
	public string $title;
	public ?string $description = null;
	public ?ThumbResource $thumb;

	public bool $is_nsfw;
	public bool $is_public;
	public bool $is_link_required;
	public bool $is_password_required;

	public bool $is_tag_album;
	public bool $has_subalbum;
	public int $num_subalbums = 0;
	public int $num_photos = 0;

	public string $created_at = '';
	private ?string $min_taken_at = null;
	private ?string $max_taken_at = null;
	public ?string $formatted_min_max = null;
	public ?string $owner = null;

	private Carbon $created_at_carbon;
	private ?Carbon $min_taken_at_carbon = null;
	private ?Carbon $max_taken_at_carbon = null;

	public AlbumRightsResource $rights;
	public ?TimelineData $timeline = null;

	public function __construct(AbstractAlbum $data)
	{
		$date_format = Configs::getValueAsString('date_format_album_thumb');

		$this->id = $data->id;
		$this->thumb = ThumbResource::fromModel($data->thumb);
		$this->title = $data->title;

		if ($data instanceof BaseSmartAlbum) {
			$policy = AlbumProtectionPolicy::ofSmartAlbum($data);
		} else {
			/** @var BaseAlbum $data */
			$this->min_taken_at_carbon = $data->min_taken_at;
			$this->max_taken_at_carbon = $data->max_taken_at;
			$this->max_taken_at = $this->max_taken_at_carbon?->format($date_format);
			$this->min_taken_at = $this->min_taken_at_carbon?->format($date_format);

			$this->formatMinMaxDate();

			$this->created_at_carbon = $data->created_at;
			$this->created_at = $this->created_at_carbon->format($date_format);
			$policy = AlbumProtectionPolicy::ofBaseAlbum($data);
			$this->description = Str::limit($data->description, 100);
			$this->owner = $data->owner->username;
		}

		if ($data instanceof Album) {
			$this->num_photos = $data->num_photos;
			$this->num_subalbums = $data->num_children;
		}

		$this->is_nsfw = $policy->is_nsfw;
		$this->is_public = $policy->is_public;
		$this->is_link_required = $policy->is_link_required;
		$this->is_password_required = $policy->is_password_required;

		$this->is_tag_album = $data instanceof TagAlbum;
		// This aims to indicate whether the current thumb is used to determine the parent.
		$this->has_subalbum = $data instanceof Album && !$data->isLeaf();

		$this->rights = new AlbumRightsResource($data);
	}

	public static function fromModel(AbstractAlbum $album): ThumbAlbumResource
	{
		return new self($album);
	}

	private function formatMinMaxDate(): void
	{
		if ($this->max_taken_at === null || $this->min_taken_at === null) {
			return;
		}
		if ($this->max_taken_at === $this->min_taken_at) {
			$this->formatted_min_max = $this->max_taken_at;

			return;
		}

		if (Configs::getValueAsEnum('thumb_min_max_order', DateOrderingType::class) === DateOrderingType::YOUNGER_OLDER) {
			$this->formatted_min_max = $this->max_taken_at . ' - ' . $this->min_taken_at;
		} else {
			$this->formatted_min_max = $this->min_taken_at . ' - ' . $this->max_taken_at;
		}
	}

	/**
	 * Accessors to the Carbon instances.
	 *
	 * @return Carbon
	 */
	public function created_at_carbon(): Carbon
	{
		return $this->created_at_carbon;
	}

	public function min_taken_at_carbon(): ?Carbon
	{
		return $this->min_taken_at_carbon;
	}

	public function max_taken_at_carbon(): ?Carbon
	{
		return $this->max_taken_at_carbon;
	}
}
