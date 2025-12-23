<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Assets\Features;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Exceptions\UnexpectedException;
use App\Jobs\UploadSizeVariantToS3Job;
use App\Models\SizeVariant;
use App\Repositories\ConfigManager;
use Illuminate\Console\Command;
use Safe\Exceptions\InfoException;
use function Safe\set_time_limit;

class MoveToS3 extends Command
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
		return parent::__construct();
	}
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:s3_migrate {limit=5 : number of photos to move to s3} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Migrate existing photos to the configured S3 bucket';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		if (Features::inactive('use-s3')) {
			$this->error('S3 support is not activated.');

			return 0;
		}
		// @codeCoverageIgnoreStart
		try {
			$limit = (int) $this->argument('limit');
			$timeout = (int) $this->argument('tm');

			try {
				set_time_limit($timeout);
			} catch (InfoException) {
				// Silently do nothing, if `set_time_limit` is denied.
			}

			$sive_variants = SizeVariant::query()
				->whereNot('type', '=', SizeVariantType::PLACEHOLDER->value)
				->where('storage_disk', '=', StorageDiskType::LOCAL->value)
				->limit($limit)
				->get();
			if (count($sive_variants) === 0) {
				$this->line('No files require migrations.');

				return 0;
			}
			$owner_id = $this->config_manager->getValueAsInt('owner_id');
			foreach ($sive_variants as $size_variant) {
				$this->line('Moving ' . $size_variant->short_path . ' to S3.');
				UploadSizeVariantToS3Job::dispatch($size_variant, $owner_id);
			}

			return 0;
		} catch (\Throwable $e) {
			throw new UnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}
}
