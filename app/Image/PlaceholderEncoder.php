<?php

namespace App\Image;

use App\Contracts\Image\MediaFile;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\InMemoryBuffer;
use App\Image\Files\TemporaryLocalFile;
use App\Models\SizeVariant;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\ImageException;
use Safe\Exceptions\StreamException;
use function Safe\imagecreatefromstring;
use function Safe\imagewebp;
use function Safe\rewind;
use function Safe\stream_filter_append;
use function Safe\stream_get_contents;

class PlaceholderEncoder
{
	private const IMAGE_QUALITY = 30;

	public function do(SizeVariant $sizeVariant): void
	{
		try {
			$inMemoryBuffer = new InMemoryBuffer();
			// Compress webp image to acceptable size for DB,
			// size_variants.short_path column allows max 255 characters.
			// We need a bit of breathing room because the file
			// may expand up to 33% when we convert to base64.
			imagewebp($this->createGdImage($sizeVariant->getFile()), $inMemoryBuffer->stream(), self::IMAGE_QUALITY);

			$base64 = new TemporaryLocalFile('');

			stream_filter_append($base64->read(), 'convert.base64-encode');
			$base64->write($inMemoryBuffer->read());
			$base64->close();

			$sizeVariant->filesize = $base64->getFilesize();
			$sizeVariant->short_path = stream_get_contents($base64->read());
			$sizeVariant->save();
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Failed to encode placeholder to base64', $e);
		}
	}

	/**
	 * Returns a GdImage object from the provided file.
	 *
	 * @param MediaFile $file
	 *
	 * @return \GdImage
	 *
	 * @throws FilesystemException
	 * @throws ImageException
	 * @throws StreamException
	 */
	private function createGdImage(MediaFile $file): \GdImage
	{
		$inMemoryBuffer = new InMemoryBuffer();

		$originalStream = $file->read();
		if (stream_get_meta_data($originalStream)['seekable']) {
			$inputStream = $originalStream;
		} else {
			// We make an in-memory copy of the provided stream,
			// because we must be able to seek/rewind the stream.
			// For example, a readable stream from a remote location (i.e.
			// a "download" stream) is only forward readable once.
			$inMemoryBuffer->write($originalStream);
			$inputStream = $inMemoryBuffer->read();
		}
		$imgBinary = stream_get_contents($inputStream);

		rewind($inputStream);
		/** @var \GdImage $referenceImage */
		$referenceImage = imagecreatefromstring($imgBinary);

		return $referenceImage;
	}
}