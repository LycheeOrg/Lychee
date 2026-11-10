<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_FRAME = 'Mod Frame';
	public const REQUIRES = 'mod_frame_enabled';
	public const COLOR = 'color';
	public const CLOCK_POSITION = 'top_left|top_right|bottom_left|bottom_right|center';
	public const INFO_POSITION = 'top_left|top_right|bottom_left|bottom_right';
	public const TIME_FORMAT = '24h|12h';
	public const SUBALBUMS_DETAILS = 'Frames opened from an album, and the default album above, pick photos from the album and all its sub-albums. The photo information can then show the album of each photo.';

	/**
	 * Builds one Frame overlay setting row; every row shares category, level and dependency.
	 * The details name the URL parameter overriding the setting on one device (Feature 088).
	 *
	 * @return array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,order:int,is_expert:bool,level:int,details:string,required_keys:string}
	 */
	private function row(string $key, string $value, string $type_range, string $description, int $order, string $example): array
	{
		$parameter = substr($key, strlen('mod_frame_'));

		return [
			'key' => $key,
			'value' => $value,
			'cat' => self::MOD_FRAME,
			'type_range' => $type_range,
			'description' => $description,
			'details' => 'Override it on one device by adding <code>?' . $parameter . '=' . $example . '</code> to the Frame URL.',
			'is_secret' => false,
			'is_expert' => false,
			'level' => 0,
			'order' => $order,
			'required_keys' => self::REQUIRES,
		];
	}

	public function getConfigs(): array
	{
		$include_subalbums = $this->row('mod_frame_include_subalbums', '0', self::BOOL, 'Album frames include photos of sub-albums', 3, '1');
		$include_subalbums['details'] = self::SUBALBUMS_DETAILS;

		return [
			$include_subalbums,
			$this->row('mod_frame_clock_time_enabled', '0', self::BOOL, 'Show the current time', 4, '1'),
			$this->row('mod_frame_clock_time_format', '24h', self::TIME_FORMAT, 'Time format', 5, '12h'),
			$this->row('mod_frame_clock_date_enabled', '0', self::BOOL, 'Show the current date', 6, '1'),
			$this->row('mod_frame_clock_position', 'bottom_right', self::CLOCK_POSITION, 'Position of the time and date', 7, 'top_right'),
			$this->row('mod_frame_clock_background_color', '#000000', self::COLOR, 'Background colour of the time and date', 8, '1e293b'),
			$this->row('mod_frame_clock_background_opacity', '50', self::PERCENT_RANGE, 'Background opacity of the time and date (%)', 9, '80'),
			$this->row('mod_frame_clock_opacity', '100', self::PERCENT_RANGE, 'Opacity of the time and date (%)', 10, '80'),
			$this->row('mod_frame_info_title_enabled', '0', self::BOOL, 'Show the photo title', 11, '1'),
			$this->row('mod_frame_info_albums_enabled', '0', self::BOOL, 'Show the albums of the photo', 12, '1'),
			$this->row('mod_frame_info_exif_enabled', '0', self::BOOL, 'Show the EXIF data of the photo', 13, '1'),
			$this->row('mod_frame_info_exif_exposure_enabled', '1', self::BOOL, 'EXIF: focal length, aperture, shutter speed and ISO', 14, '0'),
			$this->row('mod_frame_info_exif_camera_enabled', '1', self::BOOL, 'EXIF: camera and lens', 15, '0'),
			$this->row('mod_frame_info_exif_taken_at_enabled', '1', self::BOOL, 'EXIF: date taken', 16, '0'),
			$this->row('mod_frame_info_position', 'bottom_left', self::INFO_POSITION, 'Position of the photo information', 17, 'top_right'),
			$this->row('mod_frame_info_background_color', '#000000', self::COLOR, 'Background colour of the photo information', 18, '1e293b'),
			$this->row('mod_frame_info_background_opacity', '50', self::PERCENT_RANGE, 'Background opacity of the photo information (%)', 19, '80'),
			$this->row('mod_frame_info_opacity', '100', self::PERCENT_RANGE, 'Opacity of the photo information (%)', 20, '80'),
		];
	}
};
