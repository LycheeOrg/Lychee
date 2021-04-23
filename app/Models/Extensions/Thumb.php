<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Models\Extensions;

use Helpers;

class Thumb
{
	public $thumb = '';
	public $type = '';
	public $thumb2x = '';
	public $id = null;

	public function __construct(string $type, int $id)
	{
		$this->type = $type;
		$this->id = $id;
	}

	public function set_thumb2x(): void
	{
		$this->thumb2x = Helpers::ex2x($this->thumb);
	}

	public function toArray(): array
	{
		return [
			'id' => strval($this->id),
			'type' => $this->type,
			'thumb' => $this->thumb,
			'thumb2x' => $this->thumb2x,
		];
	}
}
