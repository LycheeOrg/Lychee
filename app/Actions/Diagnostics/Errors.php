<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics;

use App\Actions\Diagnostics\Pipes\Checks\AdminUserExistsCheck;
use App\Actions\Diagnostics\Pipes\Checks\AppUrlMatchCheck;
use App\Actions\Diagnostics\Pipes\Checks\BasicPermissionCheck;
use App\Actions\Diagnostics\Pipes\Checks\ConfigSanityCheck;
use App\Actions\Diagnostics\Pipes\Checks\CountSizeVariantsCheck;
use App\Actions\Diagnostics\Pipes\Checks\DBIntegrityCheck;
use App\Actions\Diagnostics\Pipes\Checks\DBSupportCheck;
use App\Actions\Diagnostics\Pipes\Checks\ForeignKeyListInfo;
use App\Actions\Diagnostics\Pipes\Checks\GDSupportCheck;
use App\Actions\Diagnostics\Pipes\Checks\ImageOptCheck;
use App\Actions\Diagnostics\Pipes\Checks\IniSettingsCheck;
use App\Actions\Diagnostics\Pipes\Checks\MigrationCheck;
use App\Actions\Diagnostics\Pipes\Checks\PHPVersionCheck;
use App\Actions\Diagnostics\Pipes\Checks\PlaceholderExistsCheck;
use App\Actions\Diagnostics\Pipes\Checks\SmallMediumExistsCheck;
use App\Actions\Diagnostics\Pipes\Checks\SupporterCheck;
use App\Actions\Diagnostics\Pipes\Checks\TimezoneCheck;
use App\Actions\Diagnostics\Pipes\Checks\UpdatableCheck;
use App\DTO\DiagnosticData;
use Illuminate\Pipeline\Pipeline;

class Errors
{
	/**
	 * The array of class pipes.
	 *
	 * @var array<int,class-string>
	 */
	private array $pipes = [
		AdminUserExistsCheck::class,
		BasicPermissionCheck::class,
		ConfigSanityCheck::class,
		DBSupportCheck::class,
		GDSupportCheck::class,
		ImageOptCheck::class,
		IniSettingsCheck::class,
		AppUrlMatchCheck::class,
		MigrationCheck::class,
		PHPVersionCheck::class,
		TimezoneCheck::class,
		UpdatableCheck::class,
		ForeignKeyListInfo::class,
		DBIntegrityCheck::class,
		SmallMediumExistsCheck::class,
		PlaceholderExistsCheck::class,
		CountSizeVariantsCheck::class,
		SupporterCheck::class,
	];

	/**
	 * Return the list of error which are currently breaking Lychee.
	 *
	 * @param string[] $skip class names of checks that will be skipped
	 *
	 * @return DiagnosticData[] array of messages
	 */
	public function get(array $skip = []): array
	{
		$filteredPipes = collect($this->pipes);
		$this->pipes = $filteredPipes->reject(fn ($p) => in_array((new \ReflectionClass($p))->getShortName(), $skip, true))->all();

		/** @var DiagnosticData[] $errors */
		$errors = [];

		return app(Pipeline::class)
			->send($errors)
			->through($this->pipes)
			->thenReturn();
	}
}
