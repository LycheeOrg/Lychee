<?php

namespace App\DTO;

use App\Actions\Photo\Strategies\AddStrategyParameters;
use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\Internal\LycheeLogicException;
use App\Image\Files\NativeLocalFile;
use App\Models\Photo;

class PhotoCreateDTO
{
	public Photo|null $photo = null;
	public Photo|null $duplicate = null;
	public Photo|null $livePartner = null;

	public function __construct(
		public AddStrategyParameters $strategyParameters,
		public NativeLocalFile $sourceFile,
		public null|AbstractAlbum $album,
		public null|int $fileLastModifiedTime = null
	) {
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
