<?php

namespace App\DTO;

abstract class ImportReport extends DTO
{
	protected string $type;

	protected function __construct(string $type)
	{
		$this->type = $type;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'type' => $this->type,
		];
	}

	abstract public function toCLIString(): string;
}
