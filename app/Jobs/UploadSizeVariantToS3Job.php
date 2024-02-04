<?php

namespace App\Jobs;

use App\Enum\StorageDiskType;
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
		Storage::disk(StorageDiskType::S3->value)->writeStream(
			$this->variant->short_path,
			Storage::disk(StorageDiskType::LOCAL->value)->readStream($this->variant->short_path)
		);

		Storage::disk(StorageDiskType::LOCAL->value)->delete($this->variant->short_path);

		$this->variant->storage_disk = StorageDiskType::S3;
		$this->variant->save();
	}
}
