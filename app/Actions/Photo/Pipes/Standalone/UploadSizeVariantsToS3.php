<?php

namespace App\Actions\Photo\Pipes\Standalone;

use App\Assets\Features;
use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Jobs\UploadSizeVariantToS3Job;
use App\Models\Configs;
use App\Models\SizeVariant;

/**
 * Upload SizeVariants to S3 once done (if enabled).
 * Note that we first create the job, then we dispatch it.
 * This allows to manage the queue properly and see it in the feedback.
 */
class UploadSizeVariantsToS3 implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		if (Features::active('use-s3')) {
			$sync = Configs::getValueAsBool('use_job_queues');

			$jobs = $state->photo->size_variants->toCollection()
				->filter(fn ($v) => $v !== null)
				->map(fn (SizeVariant $variant) => new UploadSizeVariantToS3Job($variant));

			$jobs->each(fn ($job) => $sync ? dispatch($job) : dispatch_sync($job));
		}

		return $next($state);
	}
}