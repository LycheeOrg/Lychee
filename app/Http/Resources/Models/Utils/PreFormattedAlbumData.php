<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models\Utils;

use App\Contracts\Models\AbstractAlbum;
use App\Enum\AlbumTitleColor;
use App\Enum\AlbumTitlePosition;
use App\Enum\DateOrderingType;
use App\Enum\LicenseType;
use App\Http\Resources\Models\ColourPaletteResource;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use GrahamCampbell\Markdown\Facades\Markdown;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class PreFormattedAlbumData extends Data
{
	public ?string $url;
	public string $title;
	public ?string $min_max_text = null;
	private ?string $min_taken_at = null;
	private ?string $max_taken_at = null;
	public string $album_id;
	public string $license = '';
	public int $num_children = 0;
	public int $num_photos = 0;
	public ?string $created_at = null;
	public ?string $description = null;
	public ?string $copyright = null;

	public ?ColourPaletteResource $palette = null;

	public ?AlbumTitleColor $title_color = null;
	public ?AlbumTitlePosition $title_position = null;
	public ?HeaderFocusData $header_photo_focus = null;

	public function __construct(AbstractAlbum $album, ?string $url)
	{
		$min_max_date_format = request()->configs()->getValueAsString('date_format_hero_min_max');
		$create_date_format = request()->configs()->getValueAsString('date_format_hero_created_at');
		$this->url = $url;

		$this->title = $album->get_title();
		if ($album instanceof BaseAlbum) {
			$this->min_taken_at = $album->min_taken_at?->translatedFormat($min_max_date_format);
			$this->max_taken_at = $album->max_taken_at?->translatedFormat($min_max_date_format);
			$this->formatMinMaxDate();
			$this->created_at = $album->created_at->translatedFormat($create_date_format);
			$this->description = Markdown::convert(trim($album->description ?? ''))->getContent();
			$this->copyright = $album->copyright;
		}
		if ($album instanceof Album) {
			$this->num_children = $album->num_children;
			$this->num_photos = $album->num_photos;
			$this->license = $album->license === LicenseType::NONE ? '' : $album->license->localization();
			$this->title_color = $album->title_color;
			$this->title_position = $album->title_position;
			if ($album->header_photo_focus !== null) {
				$this->header_photo_focus = new HeaderFocusData(
					x: $album->header_photo_focus['x'] ?? null,
					y: $album->header_photo_focus['y'] ?? null,
				);
			}

			if ($album->header !== null && $album->header !== null && $album->header->palette !== null) {
				$this->palette = ColourPaletteResource::fromModel($album->header->palette);
			}
		}
	}

	private function formatMinMaxDate(): void
	{
		if ($this->max_taken_at === null || $this->min_taken_at === null) {
			return;
		}
		if ($this->max_taken_at === $this->min_taken_at) {
			$this->min_max_text = $this->max_taken_at;

			return;
		}

		if (request()->configs()->getValueAsEnum('header_min_max_order', DateOrderingType::class) === DateOrderingType::YOUNGER_OLDER) {
			$this->min_max_text = $this->max_taken_at . ' - ' . $this->min_taken_at;
		} else {
			$this->min_max_text = $this->min_taken_at . ' - ' . $this->max_taken_at;
		}
	}
}
