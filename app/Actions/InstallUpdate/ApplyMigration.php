<?php

namespace App\Actions\InstallUpdate;

use App\Actions\InstallUpdate\Pipes\ArtisanKeyGenerate;
use App\Actions\InstallUpdate\Pipes\ArtisanMigrate;
use App\Actions\InstallUpdate\Pipes\ArtisanViewClear;
use App\Actions\InstallUpdate\Pipes\QueryExceptionChecker;

class ApplyMigration
{
	public array $migrationPipe = [
		ArtisanViewClear::class,
		ArtisanMigrate::class,
		QueryExceptionChecker::class,
	];

	public array $keyGenerationPipe = [
		ArtisanKeyGenerate::class,
	];
}
