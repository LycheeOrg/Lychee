<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\Insights;

use App\Enum\SizeVariantType;
use App\Models\Album;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * A small library with fixed capture times, timezones and EXIF values for
 * the Insights feature tests (Feature 085).
 *
 * | photo | type  | local capture (tz)              | size¹ | w × h¹      | album | other                         |
 * |-------|-------|---------------------------------|-------|-------------|-------|-------------------------------|
 * | p1    | jpeg  | 2025-01-01 08:30 (+09:00)       | 1000  | 6000 × 4000 | A     | located, highlighted, 2 faces |
 * | p2    | jpeg  | 2025-01-02 10:00 (UTC)          | 3000  | 4000 × 6000 | A     |                               |
 * | p3    | mp4   | 2025-01-02 18:00 (UTC)          | 10000 | 1920 × 1080 | —     | 20.4 s                        |
 * | p4    | jpeg  | 2025-03-10 12:00 (Europe/Paris) | 0     | 4032 × 3024 | B     | located, 1 face + 1 dismissed |
 * | p5    | raw   | 2024-06-15 09:00 (UTC)          | 500   | 0 × 0       | —     | no EXIF                       |
 * | p6    | jpeg  | undated                         | 2000  | 3000 × 3000 | B     |                               |
 *
 * ¹ of the original size variant. p1 is stored as 2024-12-31
 * 23:30 UTC. Album C is empty.
 */
trait InsightsDataset
{
	protected User $photographer;
	protected Album $album_a;
	protected Album $album_b;
	protected Album $album_c;
	protected Photo $p1;
	protected Photo $p2;
	protected Photo $p3;
	protected Photo $p4;
	protected Photo $p5;
	protected Photo $p6;

	protected function seedInsightsDataset(): void
	{
		$this->photographer = User::factory()->may_upload()->create();
		$this->album_a = Album::factory()->as_root()->owned_by($this->photographer)->create();
		$this->album_b = Album::factory()->as_root()->owned_by($this->photographer)->create();
		$this->album_c = Album::factory()->as_root()->owned_by($this->photographer)->create();

		$nikon = ['make' => 'NIKON CORPORATION', 'model' => 'NIKON D850', 'lens' => 'AF-S 24-70mm'];
		$iphone = ['make' => 'Apple', 'model' => 'iPhone 13 Pro'];

		$this->p1 = $this->insightsPhoto([...$nikon, 'taken_at' => $this->local('2025-01-01 08:30:00', '+09:00'), 'filesize' => 1000, 'size' => [6000, 4000], 'latitude' => '51.8', 'longitude' => '5.8', 'is_highlighted' => true, 'iso' => '100', 'aperture' => 'f/2.8', 'shutter' => '1/250 s', 'focal' => '24 mm'], $this->album_a);
		$this->p2 = $this->insightsPhoto(['make' => 'Nikon', 'model' => 'D850', 'lens' => 'AF-S 24-70mm', 'taken_at' => $this->local('2025-01-02 10:00:00', 'UTC'), 'filesize' => 3000, 'size' => [4000, 6000], 'iso' => '400', 'aperture' => 'f/4', 'shutter' => '1/60 s', 'focal' => '70 mm'], $this->album_a);
		$this->p3 = $this->insightsPhoto([...$iphone, 'type' => 'video/mp4', 'lens' => null, 'taken_at' => $this->local('2025-01-02 18:00:00', 'UTC'), 'filesize' => 10000, 'size' => [1920, 1080], 'duration' => '20.4'], null);
		$this->p4 = $this->insightsPhoto([...$iphone, 'lens' => 'iPhone 13 Pro back camera', 'taken_at' => $this->local('2025-03-10 12:00:00', 'Europe/Paris'), 'filesize' => 0, 'size' => [4032, 3024], 'latitude' => '48.8', 'longitude' => '2.3', 'iso' => '100', 'aperture' => 'f/1.5', 'shutter' => '1/120 s', 'focal' => '5.7 mm'], $this->album_b);
		$this->p5 = $this->insightsPhoto(['type' => 'image/x-canon-cr2', 'make' => null, 'model' => null, 'lens' => null, 'taken_at' => $this->local('2024-06-15 09:00:00', 'UTC'), 'filesize' => 500, 'size' => [0, 0]], null);
		$this->p6 = $this->insightsPhoto([...$nikon, 'taken_at' => null, 'initial_taken_at' => null, 'filesize' => 2000, 'size' => [3000, 3000], 'iso' => '3200', 'aperture' => '', 'shutter' => 'bulb', 'focal' => null], $this->album_b);

		$person_x = Person::factory()->create();
		$person_y = Person::factory()->create();
		Face::factory()->for_photo($this->p1)->for_person($person_x)->create();
		Face::factory()->for_photo($this->p1)->create();
		Face::factory()->for_photo($this->p4)->for_person($person_y)->create();
		Face::factory()->for_photo($this->p4)->for_person($person_x)->dismissed()->create();
	}

	/**
	 * @param array<string,mixed> $attributes
	 */
	private function insightsPhoto(array $attributes, ?Album $album): Photo
	{
		$defaults = [
			'type' => 'image/jpeg',
			'is_highlighted' => false,
			'latitude' => null,
			'longitude' => null,
			'iso' => null,
			'aperture' => null,
			'shutter' => null,
			'focal' => null,
			'duration' => null,
		];
		$factory = Photo::factory()->owned_by($this->photographer);
		if ($album !== null) {
			$factory = $factory->in($album);
		}
		$filesize = $attributes['filesize'];
		[$width, $height] = $attributes['size'];
		unset($attributes['filesize'], $attributes['size']);

		$photo = $factory->create([...$defaults, ...$attributes]);
		DB::table('size_variants')
			->where('photo_id', '=', $photo->id)
			->where('type', '=', SizeVariantType::ORIGINAL->value)
			->update(['filesize' => $filesize, 'width' => $width, 'height' => $height]);

		return $photo;
	}

	private function local(string $datetime, string $timezone): Carbon
	{
		return new Carbon($datetime, $timezone);
	}
}
