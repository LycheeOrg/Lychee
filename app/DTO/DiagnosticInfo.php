<?php

namespace App\DTO;

use App\Actions\Update\UpdateStatus;

class DiagnosticInfo extends ArrayableDTO
{
	/**
	 * @param string[]     $errors  list of error messages
	 * @param string[]     $infos   list of informational messages
	 * @param string[]     $configs list of configuration settings
	 * @param UpdateStatus $update  the update status
	 */
	public function __construct(
		public array $errors,
		public array $infos,
		public array $configs,
		public UpdateStatus $update
	) {
	}
}