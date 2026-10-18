<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Metadata\MotionPhoto;

/**
 * Result of {@see JpegXmpReader::read()}.
 */
final readonly class JpegHeader
{
	/**
	 * @param string|null $xmp               the first standard XMP packet, without its namespace prefix
	 * @param int         $image_data_offset offset of the start-of-scan marker, i.e. where the header ends
	 */
	public function __construct(
		public ?string $xmp,
		public int $image_data_offset,
	) {
	}
}
