<?php

namespace App\Actions\Photo\Strategies;

use App\Actions\Photo\Create;
use App\Actions\Photo\Extensions\ImageEditing;
use App\Actions\Photo\Extensions\VideoEditing;
use App\Exceptions\JsonError;
use App\Image\ImageHandlerInterface;
use App\Metadata\Extractor;
use App\Models\Logs;
use App\Models\Photo;
use Exception;

class StrategyPhoto extends StrategyPhotoBase
{
	use ImageEditing;
	use VideoEditing;
	public $imageHandler;
	public $import_via_symlink;

	public function __construct(
		bool $import_via_symlink
	) {
		$this->imageHandler = app(ImageHandlerInterface::class);
		$this->import_via_symlink = $import_via_symlink;
	}

	public function storeFile(Create $create)
	{
		// Import if not uploaded via web
		if (!$create->is_uploaded) {
			// TODO: use the storage facade here
			// Check if the user wants to create symlinks instead of copying the photo
			if ($this->import_via_symlink) {
				if (!symlink($create->tmp_name, $create->path)) {
					// @codeCoverageIgnoreStart
					Logs::error(__METHOD__, __LINE__, 'Could not create symlink');

					throw new JsonError('Could not create symlink!');
					// @codeCoverageIgnoreEnd
				}
			} elseif (!@copy($create->tmp_name, $create->path)) {
				// @codeCoverageIgnoreStart
				Logs::error(__METHOD__, __LINE__, 'Could not copy photo to uploads');

				throw new JsonError('Could not copy photo to uploads!');
				// @codeCoverageIgnoreEnd
			}
		} else {
			// TODO: use the storage facade here
			if (!@move_uploaded_file($create->tmp_name, $create->path)) {
				Logs::error(__METHOD__, __LINE__, 'Could not move photo to uploads');

				throw new JsonError('Could not move photo to uploads!');
			}
		}
	}

	public function hydrate(Create &$create, ?Photo &$existing = null, ?array $file = null)
	{
		// do nothing.
	}

	public function generate_thumbs(Create &$create, bool &$skip_db_entry_creation, bool &$no_error)
	{
		// Generate small files for 2 options:
		// (1) There is no Live Photo Partner
		// (2) There is a partner and we're uploading a photo
		if (($create->livePhotoPartner == null) || !(in_array($create->photo->type, $create->validVideoTypes, true))) {
			// Set orientation based on EXIF data
			// but do not rotate if the image shall not be modified
			if (
				$create->photo->type === 'image/jpeg'
				&& isset($create->info['orientation'])
				&& $create->info['orientation'] !== ''
			) {
				// If we are importing via symlink, we don't actually overwrite
				// the source but we still need to fix the dimensions.
				$pretend = (!$create->is_uploaded && $this->import_via_symlink);
				$rotation = $this->imageHandler->autoRotate($create->path, $create->info, $pretend);

				if ($rotation !== [false, false]) {
					$create->photo->width = $rotation['width'];
					$create->photo->height = $rotation['height'];

					if (!$pretend) {
						// If the image was rotated, the size may have changed.
						/* @var  Extractor $metadataExtractor */
						$metadataExtractor = resolve(Extractor::class);
						$create->photo->filesize = $metadataExtractor->filesize($create->path);
					}
				}
			}

			// Set original date
			if ($create->info['taken_at'] !== null) {
				@touch($create->path, $create->info['taken_at']->getTimestamp());
			}

			// For videos extract a frame from the middle
			$frame_tmp = '';
			if (in_array($create->photo->type, $create->validVideoTypes, true)) {
				try {
					$frame_tmp = $this->extractVideoFrame($create->photo);
				} catch (Exception $exception) {
					Logs::error(__METHOD__, __LINE__, $exception->getMessage());
				}
			}

			if ($create->kind == 'raw') {
				try {
					$frame_tmp = $this->createJpgFromRaw($create->photo);
				} catch (Exception $exception) {
					Logs::error(__METHOD__, __LINE__, $exception->getMessage());
				}
			}

			// Create Thumb
			if ($create->kind == 'raw' && $frame_tmp == '') {
				$create->photo->thumbUrl = '';
				$create->photo->thumb2x = 0;
			} elseif (!in_array($create->photo->type, $create->validVideoTypes, true) || $frame_tmp !== '') {
				if (!$this->createThumb($create->photo, $frame_tmp)) {
					Logs::error(__METHOD__, __LINE__, 'Could not create thumbnail for photo');

					throw new JsonError('Could not create thumbnail for photo!');
				}

				$create->photo->thumbUrl = basename($create->photo_Url, $create->extension) . '.jpeg';

				$this->createSmallerImages($create->photo, $frame_tmp);

				//? GoogleMicroVideoOffset
				if ($create->info['MicroVideoOffset']) {
					$this->extractVideo($create->photo, $create->info['MicroVideoOffset'], $frame_tmp);
				}

				if ($frame_tmp !== '') {
					unlink($frame_tmp);
				}
			} else {
				$create->photo->thumbUrl = '';
				$create->photo->thumb2x = 0;
			}
		} else {
			// We're uploading a video -> overwrite everything from partner
			$create->livePhotoPartner->livePhotoUrl = $create->photo->url;
			$create->livePhotoPartner->livePhotoChecksum = $create->photo->checksum;
			$no_error &= $create->livePhotoPartner->save();
			$skip_db_entry_creation = true;
		}
	}
}
