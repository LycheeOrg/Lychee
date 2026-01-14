<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\PhotoCreate;

use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryJobFile;

interface PhotoConverter
{
	public function handle(NativeLocalFile $tmp_file): TemporaryJobFile;
}
