<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Album;

/**
 * Request for the /Zip/chunks endpoint.
 *
 * Reuses all validation from ZipRequest (album_ids, photo_ids, variant).
 * The controller uses albums() and photos() to count total photos.
 */
class ZipChunksRequest extends ZipRequest
{
}
