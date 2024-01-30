<?php

namespace App\DTO;

use App\Actions\Photo\Strategies\AddStrategyParameters;
use App\Actions\Photo\Strategies\ImportMode;
use App\Contracts\Image\ImageHandlerInterface;
use App\Contracts\Image\StreamStats;
use App\Contracts\Models\AbstractAlbum;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Exceptions\Internal\LycheeLogicException;
use App\Image\Files\BaseMediaFile;
use App\Image\Files\FlysystemFile;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryLocalFile;
use App\Metadata\Extractor;
use App\Models\Photo;

class PhotoCreateDTO
{
	// Import mode.
	public ImportMode $importMode;

	// Indicates the intended owner of the image.
	public int $intendedOwnerId;

	// Indicates whether the new photo shall be starred.
	public bool $is_starred = false;

	// The extracted EXIF information (populated during init phase).
	public ?Extractor $exifInfo = null;

	// The intended parent album
	public ?AbstractAlbum $album = null;

	// The original photo source file that is imported.
	public NativeLocalFile $sourceFile;

	// Contains the final photo object that will be returned
	public Photo|null $photo = null;

	// During initial steps if a duplicate is found, it will be placed here.
	public Photo|null $duplicate = null;

	// During initial steps if liveParner is found, it will be placed here.
	public Photo|null $livePartner = null;

	// Optional last modified data if known.
	public int|null $fileLastModifiedTime = null;

	// used on duplicate path
	public bool $hasBeenReSynced = false;

	// used on standalone
	public ImageHandlerInterface|null $sourceImage = null;
	public AbstractSizeVariantNamingStrategy $namingStrategy;
	public TemporaryLocalFile|null $tmpVideoFile = null;
	public FlysystemFile $targetFile;
	public StreamStats|null $streamStat;

	// used by VideoLivePartner
	public string $videoPath;

	// used by PhotoLivepartner;
	public Photo $oldVideo;
	public BaseMediaFile $videoFile;

	public function __construct(
		AddStrategyParameters $parameters,
		NativeLocalFile $sourceFile,
		AbstractAlbum|null $album,
		int|null $fileLastModifiedTime = null
	) {
		$this->sourceFile = $sourceFile;
		$this->importMode = $parameters->importMode;
		$this->intendedOwnerId = $parameters->intendedOwnerId;
		$this->is_starred = $parameters->is_starred;
		$this->exifInfo = $parameters->exifInfo;
		$this->album = $album;
		$this->fileLastModifiedTime = $fileLastModifiedTime;
	}

	public function getPhoto(): Photo
	{
		if ($this->photo === null) {
			throw new LycheeLogicException('Photo is null!');
		}

		// Save just in case.
		$this->photo->save();

		return $this->photo;
	}
}
