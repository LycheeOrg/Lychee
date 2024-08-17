<?php

namespace App\Http\Resources\GalleryConfigs;

use App\Facades\Helpers;
use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UploadConfig extends Data
{
	public int $upload_processing_limit;
	public int $upload_chunk_size;

	public function __construct()
	{
		$this->upload_processing_limit = max(1, Configs::getValueAsInt('upload_processing_limit'));
		$chunk_size = Configs::getValueAsInt('upload_chunk_size');
		$upload_max_filesize = Helpers::convertSize(ini_get('upload_max_filesize'));
		$post_max_size = Helpers::convertSize(ini_get('post_max_size'));
		$this->upload_chunk_size = $chunk_size > 0 ? $chunk_size : min($upload_max_filesize, $post_max_size);
	}
}
