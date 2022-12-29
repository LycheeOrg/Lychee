<?php

namespace App\Actions\Diagnostics;

use App\Actions\Diagnostics\Pipes\Infos\CountForeignKeyInfo;
use App\Actions\Diagnostics\Pipes\Infos\ExtensionsInfo;
use App\Actions\Diagnostics\Pipes\Infos\InstallTypeInfo;
use App\Actions\Diagnostics\Pipes\Infos\SystemInfo;
use App\Actions\Diagnostics\Pipes\Infos\VersionInfo;
use Illuminate\Pipeline\Pipeline;

class Info
{
	/**
	 * The array of class pipes.
	 *
	 * @var array<int,class-string>
	 */
	private $pipes = [
		VersionInfo::class,
		InstallTypeInfo::class,
		SystemInfo::class,
		ExtensionsInfo::class,
		CountForeignKeyInfo::class,
	];

	/**
	 * get the basic pieces of information of the Lychee installation
	 * such as version number, commit id, operating system ...
	 *
	 * @return string[] array of messages
	 */
	public function get(): array
	{
		// Declare
		$infos = [];

		return app(Pipeline::class)
			->send($infos)
			->through($this->pipes)
			->thenReturn();
	}
}
