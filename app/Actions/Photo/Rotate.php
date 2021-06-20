<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\Checksum;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\ImageEditing;
use App\Facades\Helpers;
use App\Image\ImageHandlerInterface;
use App\Metadata\Extractor;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class Rotate
{
	use Checksum;
	use Constants;
	use ImageEditing;

	public $imageHandler;

	public function __construct()
	{
		$this->imageHandler = app(ImageHandlerInterface::class);
	}

	private function check(Photo $photo, int $direction): bool
	{
		if ($photo->isVideo()) {
			Logs::error(__METHOD__, __LINE__, 'Trying to rotate a video');

			return false;
		}

		if ($photo->live_photo_filename !== null) {
			Logs::error(__METHOD__, __LINE__, 'Trying to rotate a live photo');

			return false;
		}

		if ($photo->isRaw()) {
			Logs::error(__METHOD__, __LINE__, 'Trying to rotate a raw file');

			return false;
		}

		// direction is valid?
		if (($direction != 1) && ($direction != -1)) {
			Logs::error(__METHOD__, __LINE__, 'Direction must be 1 or -1');

			return false;
		}

		return true;
	}

	public function do(Photo $photo, int $direction)
	{
		if (!$this->check($photo, $direction)) {
			return false;
		}

		// Generate a temporary name for the rotated file.
		$big_folder = Storage::path('big/');
		$filename = $photo->filename;
		$path = $big_folder . $filename;
		$extension = Helpers::getExtension($filename);
		if (
			!($new_tmp = tempnam($big_folder, 'lychee')) ||
			!@rename($new_tmp, $new_tmp . $extension)
		) {
			Logs::notice(__METHOD__, __LINE__, 'Could not create a temporary file.');

			return false;
		}
		$new_tmp .= $extension;

		// Rotate the original image.
		if ($this->imageHandler->rotate($path, ($direction == 1) ? 90 : -90, $new_tmp) === false) {
			Logs::error(__METHOD__, __LINE__, 'Failed to rotate ' . $path);

			return false;
		}

		// We will use new names to avoid problems with image caching.
		$new_basename = substr($this->checksum($new_tmp), 0, 32);
		$new_filename = $new_basename . $extension;
		$new_path = $big_folder . $new_filename;

		// Rename the temporary file, now that we know its final name.
		if (!@rename($new_tmp, $new_path)) {
			Logs::error(__METHOD__, __LINE__, 'Failed to rename ' . $new_tmp);

			return false;
		}

		$photo->filename = $new_filename;
		$old_width = $photo->width;
		$photo->width = $photo->height;
		$photo->height = $old_width;

		// The file size may have changed after the rotation.
		/* @var  Extractor $metadataExtractor */
		$metadataExtractor = resolve(Extractor::class);
		$photo->filesize = $metadataExtractor->filesize($new_path);
		// Also restore the original date.
		if ($photo->taken_at !== null) {
			@touch($new_path, $photo->taken_at->getTimestamp());
		}

		// Delete all old image files, including the original.
		$sizeVariants = $photo->size_variants;
		if ($sizeVariants->getThumb()) {
			@unlink(Storage::path($sizeVariants->getThumb()->getUrl()));
		}
		if ($sizeVariants->getThumb2x()) {
			@unlink(Storage::path($sizeVariants->getThumb2x()->getUrl()));
		}
		if ($sizeVariants->getSmall()) {
			@unlink(Storage::path($sizeVariants->getSmall()->getUrl()));
		}
		if ($sizeVariants->getSmall2x()) {
			@unlink(Storage::path($sizeVariants->getSmall2x()->getUrl()));
		}
		if ($sizeVariants->getMedium()) {
			@unlink(Storage::path($sizeVariants->getMedium()->getUrl()));
		}
		if ($sizeVariants->getMedium2x()) {
			@unlink(Storage::path($sizeVariants->getMedium2x()->getUrl()));
		}
		@unlink($path);

		// Create new thumbs and intermediate sizes.
		if ($this->createThumb($photo) === false) {
			Logs::error(__METHOD__, __LINE__, 'Could not create thumbnail for photo');
		} else {
			$photo->thumb_filename = $new_basename . '.jpeg';
		}
		$this->createSmallerImages($photo);

		// Finally save the updated photo.
		$photo->save();

		// Deal with duplicates.  We simply update all of them to match.
		Photo::query()
			->where('checksum', $photo->checksum)
			->where('id', '<>', $photo->id)
			->update([
				'filename' => $photo->filename,
				'width' => $photo->width,
				'height' => $photo->height,
				'filesize' => $photo->filesize,
				'thumb_filename' => $photo->thumb_filename,
				'thumb2x' => $photo->thumb2x,
				'small_width' => $photo->small_width,
				'small_height' => $photo->small_height,
				'small2x_width' => $photo->small2x_width,
				'small2x_height' => $photo->small2x_height,
				'medium_width' => $photo->medium_width,
				'medium_height' => $photo->medium_height,
				'medium2x_width' => $photo->medium2x_width,
				'medium2x_height' => $photo->medium2x_height,
			]);

		return true;
	}
}
