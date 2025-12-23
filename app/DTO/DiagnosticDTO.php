<?php

namespace App\DTO;

use App\Repositories\ConfigManager;
use LycheeVerify\Contract\VerifyInterface;

/**
 * @template T of string|DiagnosticData
 */
class DiagnosticDTO
{
	/**
	 * @param VerifyInterface $verify
	 * @param ConfigManager   $config_manager
	 * @param T[]             $data
	 *
	 * @return void
	 */
	public function __construct(
		public readonly VerifyInterface $verify,
		public readonly ConfigManager $config_manager,
		public array $data,
	) {
	}
}