<?php

namespace App\Actions\Photo\Extensions;

use App\Exceptions\ExternalComponentMissingException;
use App\Image\MediaFile;
use App\Image\NativeLocalFile;
use App\Image\TemporaryLocalFile;
use App\Models\Photo;
use Illuminate\Http\UploadedFile;

/**
 * Class SourceFileInfo.
 */
class SourceFileInfo
{
	/** @var string the original name of the media file or title of the media */
	protected string $originalName;
	/** @var string the original extension incl. a preceding dot */
	protected string $originalExtension;
	protected string $originalMimeType;
	protected MediaFile $file;

	/**
	 * SourceFileInfo constructor.
	 *
	 * @param string    $originalName      the name of the original media file
	 *                                     or title of the media
	 * @param string    $originalExtension the extension of the original file
	 *                                     incl. a preceding dot as reported
	 *                                     by the client (in case of an
	 *                                     upload) or by the (remote) server
	 *                                     (in case of an import)
	 * @param string    $originalMimeType  the original mime-type as reported
	 *                                     by the client or by the (remote)
	 *                                     server
	 * @param MediaFile $file              the media file
	 */
	protected function __construct(string $originalName, string $originalExtension, string $originalMimeType, MediaFile $file)
	{
		$this->originalName = $originalName;
		$this->originalExtension = $originalExtension;
		$this->originalMimeType = $originalMimeType;
		$this->file = $file;
	}

	/**
	 * Creates a new instance which is suitable, if the source file is a
	 * temporary file.
	 *
	 * @param string             $originalName      the name of the original
	 *                                              media file or title of the
	 *                                              media
	 * @param string             $originalExtension the extension of the
	 *                                              original file incl. a
	 *                                              preceding dot
	 * @param TemporaryLocalFile $file              the temporary file
	 *
	 * @return SourceFileInfo the new instance
	 */
	public static function createByTempFile(string $originalName, string $originalExtension, TemporaryLocalFile $file): SourceFileInfo
	{
		return new self($originalName, $originalExtension, $file->getMimeType(), $file);
	}

	/**
	 * Creates a new instance which is suitable, if the source file is a
	 * local file.
	 *
	 * @param NativeLocalFile $file the local source file
	 *
	 * @return SourceFileInfo the new instance
	 */
	public static function createByLocalFile(NativeLocalFile $file): SourceFileInfo
	{
		return new self(
			$file->getBasename(),
			'.' . $file->getExtension(),
			$file->getMimeType(),
			$file
		);
	}

	/**
	 * Creates a new instance which is suitable, if the source file is a
	 * native file on the server.
	 *
	 * @param Photo $photo the photo
	 *
	 * @return SourceFileInfo the new instance
	 */
	public static function createByPhoto(Photo $photo): SourceFileInfo
	{
		$file = $photo->size_variants->getOriginal()->getFile();

		return new self(
			$photo->title,
			$file->getExtension(),
			$photo->type,
			$file
		);
	}

	/**
	 * Creates a new instance which is suitable, if the source file is an
	 * uploaded file from a remote HTTP client.
	 *
	 * @param UploadedFile $file the uploaded file
	 *
	 * @return SourceFileInfo the new instance
	 *
	 * @throws ExternalComponentMissingException
	 */
	public static function createByUploadedFile(UploadedFile $file): SourceFileInfo
	{
		try {
			$fallbackTitle = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

			return new self(
				$fallbackTitle,
				'.' . $file->getClientOriginalExtension(),
				$file->getMimeType(),
				NativeLocalFile::createFromUploadedFile($file)
			);
		} catch (\LogicException $e) {
			throw new ExternalComponentMissingException('MIME component not installed', $e);
		}
	}

	/**
	 * Returns the original name of the source file or its title.
	 *
	 * The original name is either the basename of the originally
	 * uploaded/downloaded file or - if the source file is already provided
	 * by a photo in the DB - the user-defined title of the source photo.
	 *
	 * The original name is used as a fallback title for the imported photo
	 * in case the media file does not provide a title via EXIF data.
	 *
	 * @return string the original name of the media file or the title of the media
	 */
	public function getOriginalName(): string
	{
		return $this->originalName;
	}

	/**
	 * Returns the original extension of the source file.
	 *
	 * The original file extension is provided as part of the client-side
	 * file name during upload or by the filename when downloaded from a
	 * remote server.
	 * This file extension is used as a fallback to determine the type of
	 * file, if no better information (i.e. mimetype) can be provided or
	 * extracted from the file.
	 *
	 * @return string the original extension of the file
	 */
	public function getOriginalExtension(): string
	{
		return $this->originalExtension;
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
	 * Returns the media file.
	 *
	 * @return MediaFile the media file
	 */
	public function getFile(): MediaFile
	{
		return $this->file;
	}
}