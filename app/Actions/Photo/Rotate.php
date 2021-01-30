<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\ImageEditing;
use App\Assets\Helpers;
use App\Image\ImageHandlerInterface;
use App\Metadata\Extractor;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class Rotate
{
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

		// We will use new names to avoid problems with image caching.
		$new_prefix = md5(microtime());
		$url = $photo->url;
		$new_url = $new_prefix . Helpers::getExtension($url);
		$big_folder = Storage::path('big/');
		$path = $big_folder . $url;
		$new_path = $big_folder . $new_url;

		// Rotate the original image.
		if ($this->imageHandler->rotate($path, ($direction == 1) ? 90 : -90, $new_path) === false) {
			Logs::error(__METHOD__, __LINE__, 'Failed to rotate ' . $path);

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
		if ($photo->small != '') {
			@unlink(Storage::path('small/' . $url));
			$photo->small = '';
			if ($photo->small2x != '') {
				@unlink(Storage::path('small/' . Helpers::ex2x($url)));
				$photo->small2x = '';
			}
		}
		if ($photo->medium != '') {
			@unlink(Storage::path('medium/' . $url));
			$photo->medium = '';
			if ($photo->medium2x != '') {
				@unlink(Storage::path('medium/' . Helpers::ex2x($url)));
				$photo->medium2x = '';
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
				'small' => $photo->small,
				'small2x' => $photo->small2x,
				'medium' => $photo->medium,
				'medium2x' => $photo->medium2x,
			]);

		return true;
	}
}
