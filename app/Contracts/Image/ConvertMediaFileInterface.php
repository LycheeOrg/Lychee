<?php

namespace App\Contracts\Image;

use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryJobFile;

interface ConvertMediaFileInterface
{
	public function handle(NativeLocalFile $tmpFile): TemporaryJobFile;

	public function storeNewImage(\Imagick $imageInstance, string $storeToPath);

	public function deleteOldFile(string $path);
}