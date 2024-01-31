<?php

namespace App\Jobs;

use App\Enum\ExternalStorageProvider;
use App\Models\SizeVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UploadSizeVariantToS3Job implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected SizeVariant $variant;

	public function __construct(SizeVariant $variant)
	{
		$this->variant = $variant;
	}

	public function handle(): void
	{
		Storage::disk('s3')->writeStream(
			$this->variant->short_path,
			Storage::disk('images')->readStream($this->variant->short_path)
		);

		Storage::disk('images')->delete($this->variant->short_path);

		$this->variant->external_storage = ExternalStorageProvider::S3;
		$this->variant->save();
	}
}
