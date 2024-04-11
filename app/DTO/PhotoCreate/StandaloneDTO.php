<?php

namespace App\DTO\PhotoCreate;

use App\Contracts\Image\ImageHandlerInterface;
use App\Contracts\Image\StreamStats;
use App\Contracts\Models\AbstractAlbum;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Image\Files\FlysystemFile;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryLocalFile;
use App\Metadata\Extractor;
use App\Models\Photo;

class StandaloneDTO
{
	public readonly bool $shallImportViaSymlink;
	public readonly bool $shallDeleteImported;

	// The resulting photo
	public Photo $photo;

	// Indicates the intended owner of the image.
	public readonly int $intendedOwnerId;

	// Indicates whether the new photo shall be starred.
	public readonly bool $is_starred;

	// The intended parent album
	public readonly ?AbstractAlbum $album;

	// The original photo source file that is imported.
	public readonly NativeLocalFile $sourceFile;

	// The extracted EXIF information (populated during init phase).
	public readonly Extractor $exifInfo;

	public ImageHandlerInterface|null $sourceImage = null;
	public AbstractSizeVariantNamingStrategy $namingStrategy;
	public TemporaryLocalFile|null $tmpVideoFile = null;
	public FlysystemFile $targetFile;
	public StreamStats|null $streamStat;

	public function __construct(InitDTO $initDTO)
	{
		$this->photo = new Photo();
		$this->sourceFile = $initDTO->sourceFile;
		$this->is_starred = $initDTO->is_starred;
		$this->exifInfo = $initDTO->exifInfo;
		$this->album = $initDTO->album;
		$this->intendedOwnerId = $initDTO->intendedOwnerId;
		$this->shallImportViaSymlink = $initDTO->importMode->shallImportViaSymlink;
		$this->shallDeleteImported = $initDTO->importMode->shallDeleteImported;
	}
}
