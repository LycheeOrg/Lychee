<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Pipes\Init;
use App\Assets\Features;
use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\DTO\ImportParam;
use App\DTO\PhotoCreate\InitDTO;
use App\Image\Files\NativeLocalFile;
use App\Legacy\Actions\Photo\Create as LegacyPhotoCreate;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pipeline\Pipeline;

class Create
{
	/** @var ImportParam the strategy parameters prepared and compiled by this class */
	protected ImportParam $strategyParameters;

	public function __construct(?ImportMode $importMode, int $intendedOwnerId)
	{
		$this->strategyParameters = new ImportParam($importMode, $intendedOwnerId);
	}

	/**
	 * Adds/imports the designated source file to Lychee.
	 *
	 * Depending on the type and origin of the source file as well as
	 * depending on operational settings, this method applies different
	 * strategies.
	 * This method may create a new database entry or update an existing
	 * database entry.
	 *
	 * @param NativeLocalFile    $sourceFile           the source file
	 * @param int|null           $fileLastModifiedTime the timestamp to use if there's no creation date in Exif
	 * @param AbstractAlbum|null $album                the targeted parent album
	 *
	 * @return Photo the newly created or updated photo
	 *
	 * @throws ModelNotFoundException
	 * @throws LycheeException
	 */
	public function add(NativeLocalFile $sourceFile, ?AbstractAlbum $album, ?int $fileLastModifiedTime = null): Photo
	{
		if (Features::inactive('create-photo-via-pipes')) {
			$oldCodePath = new LegacyPhotoCreate($this->strategyParameters->importMode, $this->strategyParameters->intendedOwnerId);

			return $oldCodePath->add($sourceFile, $album, $fileLastModifiedTime);
		}

		$initDTO = new InitDTO(
			parameters: $this->strategyParameters,
			sourceFile: $sourceFile,
			album: $album,
			fileLastModifiedTime: $fileLastModifiedTime
		);

		/** @var InitDTO $initDTO */
		$initDTO = app(Pipeline::class)
			->send($initDTO)
			->through([
				Init\AssertSupportedMedia::class,
				Init\FetchLastModifiedTime::class,
				Init\InitParentAlbum::class,
				Init\LoadFileMetadata::class,
				Init\FindDuplicate::class,
				Init\FindLivePartner::class,
			])
			->thenReturn();

		/** @var Pipeline $nextPipe */
		// $nextPipe = app(Pipeline::class)
		// 	->send($photoDTO);

		$oldCodePath = new LegacyPhotoCreate($this->strategyParameters->importMode, $this->strategyParameters->intendedOwnerId);

		return $oldCodePath->add($sourceFile, $album, $fileLastModifiedTime);
	}
}
