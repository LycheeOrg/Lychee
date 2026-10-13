<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Frame;

use App\Models\Photo;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Photo information for the Frame info box (Feature 088, DO-088-04).
 *
 * Every value the privacy rules allow is sent, whatever the overlay
 * switches say: the v8 Frame decides what to show (ADR-088-01).
 * A value is null when hidden or missing.
 */
#[TypeScript()]
class FramePhotoData extends Data
{
	/**
	 * @param string|null        $title
	 * @param array<int,string>|null $albums   titles of the photo's browsable albums, null when the frame does not offer the album line
	 * @param string|null        $focal
	 * @param string|null        $aperture
	 * @param string|null        $shutter
	 * @param string|null        $iso
	 * @param string|null        $make
	 * @param string|null        $model
	 * @param string|null        $lens
	 * @param string|null        $taken_at
	 */
	public function __construct(
		public ?string $title,
		public ?array $albums,
		public ?string $focal,
		public ?string $aperture,
		public ?string $shutter,
		public ?string $iso,
		public ?string $make,
		public ?string $model,
		public ?string $lens,
		public ?string $taken_at,
	) {
	}

	/**
	 * @param Photo                  $photo
	 * @param array<int,string>|null $albums           see the constructor
	 * @param bool                   $is_exif_visible  false when `display_exif_data` is off
	 * @param bool                   $is_title_visible false for guests when `file_name_hidden` is on
	 * @param string                 $taken_at_format  PHP date format of `date_format_photo_overlay`
	 */
	public static function fromPhoto(Photo $photo, ?array $albums, bool $is_exif_visible, bool $is_title_visible, string $taken_at_format): self
	{
		return new self(
			title: self::shown($is_title_visible, $photo->title),
			albums: $albums,
			focal: self::shown($is_exif_visible, $photo->focal),
			aperture: self::shown($is_exif_visible, $photo->aperture),
			shutter: self::shown($is_exif_visible, $photo->shutter),
			iso: self::shown($is_exif_visible, self::iso($photo->iso)),
			make: self::shown($is_exif_visible, $photo->make),
			model: self::shown($is_exif_visible, $photo->model),
			lens: self::shown($is_exif_visible, $photo->lens),
			taken_at: $photo->taken_at?->format($taken_at_format),
		);
	}

	/**
	 * The value when visible and not empty, null otherwise.
	 */
	private static function shown(bool $is_visible, ?string $value): ?string
	{
		return ($is_visible && $value !== null && $value !== '') ? $value : null;
	}

	private static function iso(?string $iso): ?string
	{
		return ($iso === null || $iso === '') ? null : sprintf(__('gallery.photo.details.iso'), $iso);
	}
}
