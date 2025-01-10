<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Extensions;

use App\Image\Files\BaseMediaFile;

/**
 * Class ArchiveFileInfo.
 *
 * This class keeps the compiled information about a media file which are
 * required to offer a file download to a client or to add the file to an
 * archive.
 * The essential attributes are:
 *
 *  - _Base Filename:_ The base filename is a filename without any preceding
 *    directories nor extension.
 *    The base filename is used to construct a "meaningful" filename for
 *    downloading or archiving.
 *    Typically, the base filename is constructed based on the title of the
 *    photo.
 *  - _Base Filename Addon:_ The addon to the base filename is used to encode
 *    some additional information about the photo into the filename.
 *    The addon also allows generating different filenames for different
 *    variants of the same photo.
 *    Typically, the addon stars with a hyphen followed by some keyword or the
 *    dimension of the photo (e.g. `'-medium-1280x800'`, `'-large-8692x2048'`,
 *    ...).
 *  - _Extension_: The extension is the extension of the filename incl. a
 *    starting dot (e.g. `'.jpg'`).
 *  - _Full Path_: The full path is the absolute path to the source media file.
 *    Note that the full path does not necessarily contains the base filename,
 *    because the source file might be named completely differently.
 */
final readonly class ArchiveFileInfo
{
	/**
	 * ArchiveFileInfo constructor.
	 *
	 * The base file name should be used to create a "meaningful" filename
	 * which is offered to the client for download or put into the archive.
	 *
	 * The addon enables to create different filenames for different variants
	 * of the same photo.
	 *
	 * @param string        $baseFilename      the base filename (without directory
	 *                                         and extension)
	 * @param string        $baseFilenameAddon the "addon" to the base filename
	 * @param BaseMediaFile $file              the source file
	 */
	public function __construct(
		private string $baseFilename,
		private string $baseFilenameAddon,
		public BaseMediaFile $file)
	{
	}

	/**
	 * Returns the filename as it should be advertised to the downloading
	 * client or put into the archive.
	 *
	 * @param string $extraAddon an extra addon which should be added to the filename
	 *
	 * @return string the filename
	 */
	public function getFilename(string $extraAddon = ''): string
	{
		return $this->baseFilename . $this->baseFilenameAddon . $extraAddon . $this->file->getExtension();
	}
}
