<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Image;

/**
 * Class `StreamStat` holds statistics about a read/written (image) stream.
 *
 * Traditionally, Lychee used `filesize()` and `sha1_file()` to get the
 * size and checksum of an image.
 * However, these methods require a file path which is not available with
 * generic streams.
 * This class provides these values which are collected while the stream
 * is "on the fly".
 *
 * @property int    $bytes;
 * @property string $checksum;
 */
interface StreamStats
{
}
