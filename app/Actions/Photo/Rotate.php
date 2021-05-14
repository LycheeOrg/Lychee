<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\Checksum;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\ImageEditing;
use App\Image\ImageHandlerInterface;
use App\Metadata\Extractor;
use App\Models\Logs;
use App\Models\Photo;
use Helpers;
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

		if ($photo->livePhotoUrl !== null) {
			Logs::error(__METHOD__, __LINE__, 'Trying to rotate a live photo');

			return false;
		}

		if ($photo->type == 'raw') {
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
		$url = $photo->url;
		$path = $big_folder . $url;
		$extension = Helpers::getExtension($url);
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
		$new_prefix = substr($this->checksum($new_tmp), 0, 32);
		$new_url = $new_prefix . $extension;
		$new_path = $big_folder . $new_url;

		// Rename the temporary file, now that we know its final name.
		if (!@rename($new_tmp, $new_path)) {
			Logs::error(__METHOD__, __LINE__, 'Failed to rename ' . $new_tmp);

			return false;
		}

		$photo->url = $new_url;
		$old_width = $photo->width;
		$photo->width = $photo->height;
		$photo->height = $old_width;

		// The file size may have changed after the rotation.
		$metadataExtractor = resolve(Extractor::class);
		$info = [];
		$metadataExtractor->size($info, $new_path);
		$photo->size = $info['size'];
		// Also restore the original date.
		if ($photo->takestamp) {
			@touch($new_path, strtotime($photo->takestamp));
		}

		// Delete all old image files, including the original.
		if ($photo->thumbUrl != '') {
			@unlink(Storage::path('thumb/' . $photo->thumbUrl));
			if ($photo->thumb2x != 0) {
				@unlink(Storage::path('thumb/' . Helpers::ex2x($photo->thumbUrl)));
				$photo->thumb2x = 0;
			}
			$photo->thumbUrl = '';
		}
		if ($photo->small_width !== null) {
			@unlink(Storage::path('small/' . $url));
			$photo->small_width = null;
			$photo->small_height = null;
			if ($photo->small2x_width !== null) {
				@unlink(Storage::path('small/' . Helpers::ex2x($url)));
				$photo->small2x_width = null;
				$photo->small2x_height = null;
			}
		}
		if ($photo->medium_width !== null) {
			@unlink(Storage::path('medium/' . $url));
			$photo->medium_width = null;
			$photo->medium_height = null;
			if ($photo->medium2x_width !== null) {
				@unlink(Storage::path('medium/' . Helpers::ex2x($url)));
				$photo->medium2x_width = null;
				$photo->medium2x_height = null;
			}
		}
		@unlink($path);

		// Create new thumbs and intermediate sizes.
		if ($this->createThumb($photo) === false) {
			Logs::error(__METHOD__, __LINE__, 'Could not create thumbnail for photo');
		} else {
			$photo->thumbUrl = $new_prefix . '.jpeg';
		}
		$this->createSmallerImages($photo);

		// Finally save the updated photo.
		$photo->save();

		// Deal with duplicates.  We simply update all of them to match.
		Photo::where('checksum', $photo->checksum)->where('id', '<>', $photo->id)->update(
			[
				'url' => $photo->url,
				'width' => $photo->width,
				'height' => $photo->height,
				'size' => $photo->size,
				'thumbUrl' => $photo->thumbUrl,
				'thumb2x' => $photo->thumb2x,
				'small_width' => $photo->small_width,
				'small_height' => $photo->small_height,
				'small2x_width' => $photo->small2x_width,
				'small2x_height' => $photo->small2x_height,
				'medium_width' => $photo->medium_width,
				'medium_height' => $photo->medium_height,
				'medium2x_width' => $photo->medium2x_width,
				'medium2x_height' => $photo->medium2x_height,
			]
		);

		return true;
	}
}
