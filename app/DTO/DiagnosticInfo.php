<?php

namespace App\DTO;

class DiagnosticInfo extends ArrayableDTO
{
	/**
	 * @param string[] $errors  list of error messages
	 * @param string[] $infos   list of informational messages
	 * @param string[] $configs list of configuration settings
	 * @param int      $update  the update status, see
	 *                          {@link \App\Actions\Update\Check::getCode()}
	 */
	public function __construct(
		public array $errors,
		public array $infos,
		public array $configs,
		public int $update
	) {
	}
}