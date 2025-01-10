<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Http\Resources\GalleryConfigs\FooterConfig;
use App\Http\Resources\GalleryConfigs\InitConfig;
use App\Http\Resources\GalleryConfigs\PhotoLayoutConfig;
use App\Http\Resources\GalleryConfigs\UploadConfig;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for the config.
 */
class ConfigController extends Controller
{
	/**
	 * Return global gallery config.
	 *
	 * @return InitConfig
	 */
	public function getInit(): InitConfig
	{
		return new InitConfig();
	}

	/**
	 * Return gallery layout info.
	 */
	public function getGalleryLayout(): PhotoLayoutConfig
	{
		return new PhotoLayoutConfig();
	}

	/**
	 * Return the configuration of the uploader.
	 *
	 * @return UploadConfig
	 */
	public function getUploadCOnfig(): UploadConfig
	{
		return new UploadConfig();
	}

	/**
	 * Return the Footer data.
	 *
	 * @return FooterConfig
	 */
	public function getFooter(): FooterConfig
	{
		return new FooterConfig();
	}
}