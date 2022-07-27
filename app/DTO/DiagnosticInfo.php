<?php

namespace App\DTO;

class DiagnosticInfo extends DTO
{
	/** @var string[] list of error messages */
	public array $errors;

	/** @var string[] list of informational messages */
	public array $infos;

	/** @var string[] list of configuration settings */
	public array $configs;

	/** @var int the update status, see {@link \App\Actions\Update\Check::getCode()} */
	public int $update;

	/**
	 * @param string[] $errors  list of error messages
	 * @param string[] $infos   list of informational messages
	 * @param string[] $configs list of configuration settings
	 * @param int      $update  the update status, see
	 *                          {@link \App\Actions\Update\Check::getCode()}
	 */
	public function __construct(array $errors, array $infos, array $configs, int $update)
	{
		$this->errors = $errors;
		$this->infos = $infos;
		$this->configs = $configs;
		$this->update = $update;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'errors' => $this->errors,
			'infos' => $this->infos,
			'configs' => $this->configs,
			'update' => $this->update,
		];
	}
}