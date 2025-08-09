<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Diagnostics;

use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ErrorLine extends Data
{
	public MessageType $type;
	public string $message;
	public string $from;
	#[LiteralTypeScriptType('string[]')]
	public array $details;

	/**
	 * Create a Diagnostic Info.
	 *
	 * @param MessageType $type
	 * @param string      $message
	 * @param string      $from
	 * @param string[]    $details
	 */
	public function __construct(
		MessageType $type,
		string $message,
		string $from,
		array $details = [],
	) {
		$this->type = $type;
		$this->message = $message;
		$this->from = $from;
		$this->details = $details;
	}

	public static function fromObject(DiagnosticData $line): ErrorLine
	{
		return new self(
			$line->type,
			$line->message,
			str_replace('App\\Actions\\Diagnostics\\Pipes\\Checks\\', '', $line->from),
			$line->details,
		);
	}
}
