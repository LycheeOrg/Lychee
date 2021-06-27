<?php

namespace App\Actions\Photo\Extensions;

use App\Facades\Helpers;

/**
 * Class SourceFileInfo.
 */
class SourceFileInfo
{
	protected string $originalFilename;
	protected string $originalMimeType;
	protected string $tmpFullPath;

	/**
	 * SourceFileInfo constructor.
	 *
	 * @param string $originalFilename the original filename as reported by
	 *                                 the client (in case of an upload) or by
	 *                                 the (remote) server (in case of an
	 *                                 import)
	 * @param string $originalMimeType the original mime-type as reported by
	 *                                 the client or by the (remote) server
	 * @param string $tmpFullPath      the temporary location of the file
	 *                                 after upload from the client or
	 *                                 fetching from the (remote) server
	 */
	public function __construct(string $originalFilename, string $originalMimeType, string $tmpFullPath)
	{
		$this->originalFilename = $originalFilename;
		$this->originalMimeType = $originalMimeType;
		$this->tmpFullPath = $tmpFullPath;
	}

	/**
	 * Returns the original filename of the source file.
	 *
	 * Note that this filename differs from the final filename which Lychee
	 * uses to store the file in the image storage.
	 *
	 * This attribute has previously been called `name` in an anonymous array.
	 *
	 * @return string the original filename from the client side before upload
	 */
	public function getOriginalFilename(): string
	{
		return $this->originalFilename;
	}

	/**
	 * Returns the mime type of the source file.
	 *
	 * Note that this mime-type may differ from the mime-type which Lychee
	 * extracts through the media extractor from the file.
	 * It is the mime-type as reported by the client (in case of an upload)
	 * or the (remote) server (in case of an import).
	 * This mime-type may even be wrong, if the client or (remote) server is
	 * buggy and reports an erroneous mime-type.
	 *
	 * This attribute has previously been called `type` in an anonymous array.
	 *
	 * @return string the mime type
	 */
	public function getOriginalMimeType(): string
	{
		return $this->originalMimeType;
	}

	/**
	 * Returns the path at which Lychee has temporarily stored
	 * the uploaded or fetched file.
	 *
	 * This attribute has previously been called `tmp_name` in an anonymous
	 * array.
	 *
	 * @return string the mime type
	 */
	public function getTmpFullPath(): string
	{
		return $this->tmpFullPath;
	}

	/**
	 * Returns the file extension of the original source file.
	 *
	 * @return string the original file extension with a preceding dot
	 */
	public function getOriginalFileExtension(): string
	{
		return Helpers::getExtension($this->originalFilename, false);
	}
}