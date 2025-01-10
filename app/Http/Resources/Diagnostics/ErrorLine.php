<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Diagnostics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ErrorLine extends Data
{
	public const TYPE_WARNING = 'warning';
	public const TYPE_INFO = 'info';
	public const TYPE_ERROR = 'error';

	public string $type;
	public string $line;

	public function __construct(string $line)
	{
		if (\Str::startsWith($line, 'Warning: ')) {
			$this->type = self::TYPE_WARNING;
			$this->line = \Str::substr($line, 9);
		}

		if (\Str::startsWith($line, 'Info: ')) {
			$this->type = self::TYPE_INFO;
			$this->line = \Str::substr($line, 6);
		}

		if (\Str::startsWith($line, 'Error: ')) {
			$this->type = self::TYPE_ERROR;
			$this->line = \Str::substr($line, 7);
		}
	}

	public static function fromString(string $line): ErrorLine
	{
		return new self($line);
	}
}
