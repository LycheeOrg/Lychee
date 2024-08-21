<?php

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\InMemoryBuffer;
use App\Image\Files\TemporaryLocalFile;
use function Safe\imagecreatefromstring;
use function Safe\imagewebp;
use function Safe\rewind;
use function Safe\stream_filter_append;
use function Safe\stream_get_contents;

class EncodePlaceholder implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		try {
			$inMemoryBuffer = new InMemoryBuffer();
			$placeholder = $state->getPhoto()->size_variants->getPlaceholder();
			$file = $placeholder?->getFile();
			// Do not attempt to encode placeholders if they were not generated
			if ($placeholder === null) {
				return $next($state);
			}

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

			// Compress webp image to acceptable size for DB,
			// size_variants.short_path column allows max 255B.
			// We need a bit of breathing room because the file
			// may expand up to 33% when we convert to base64.
			imagewebp($referenceImage, $inMemoryBuffer->stream(), 30);

			$base64 = new TemporaryLocalFile('.txt');

			stream_filter_append($base64->read(), 'convert.base64-encode');
			$base64->write($inMemoryBuffer->read());
			$base64->close();

			$placeholder->filesize = $base64->getFilesize();
			$placeholder->short_path = stream_get_contents($base64->read());
			// delete original file since we now have no reference to it
			$placeholder->getFile()->delete();
			$placeholder->save();

			return $next($state);
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Failed to encode placeholder to base64', $e);
		}
	}
}