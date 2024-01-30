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
	public Photo|null $photo = null;
	public Photo|null $duplicate = null;
	public Photo|null $livePartner = null;

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

	public ImportMode $importMode;

	/** @var int Indicates the intended owner of the image. */
	public int $intendedOwnerId;

	/** @var AbstractAlbum|null the intended parent album */
	public ?AbstractAlbum $album = null;

	/** @var bool indicates whether the new photo shall be starred */
	public bool $is_starred = false;

	/** @var Extractor|null the extracted EXIF information */
	public ?Extractor $exifInfo = null;

	public function __construct(
		AddStrategyParameters $parameters,
		public NativeLocalFile $sourceFile,
		AbstractAlbum|null $album,
		public int|null $fileLastModifiedTime = null
	) {
		$this->importMode = $parameters->importMode;
		$this->intendedOwnerId = $parameters->intendedOwnerId;
		$this->is_starred = $parameters->is_starred;
		$this->exifInfo = $parameters->exifInfo;
		$this->album = $album;
	}

	public function getPhoto(): Photo
	{
		if ($this->photo === null) {
			throw new LycheeLogicException('Photo is null!');
		}

		$this->photo->save();

		return $this->photo;
	}
}
