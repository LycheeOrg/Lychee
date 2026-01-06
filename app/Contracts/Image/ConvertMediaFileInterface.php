<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Image;

use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryJobFile;
use Maestroerror\HeicToJpg;

interface ConvertMediaFileInterface
{
	public function handle(NativeLocalFile $tmp_file): TemporaryJobFile;

	public function storeNewImage(\Imagick|HeicToJpg $image_instance, string $store_to_path);

	public function deleteOldFile(string $path);
}