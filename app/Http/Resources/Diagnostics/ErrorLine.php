<?php

namespace App\Http\Resources\Diagnostics;

use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ErrorLine extends Data
{
	/**
	 * Create a Diagnostic Info.
	 *
	 * @param MessageType  $type
	 * @param string       $message
	 * @param class-string $from
	 * @param string[]     $details
	 */
	public function __construct(
		public MessageType $type,
		public string $message,
		public string $from,
		public array $details = [],
	) {
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
