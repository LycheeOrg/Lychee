<?php

namespace App\Http\Resources\Diagnostics;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ErrorsResource extends Data
{
	/** @var ErrorLine[] */
	public array $errors;

	/**
	 * Constructor.
	 *
	 * @param string[] $errors
	 */
	public function __construct(array $errors)
	{
		$this->errors = collect($errors)->map(fn ($line) => new ErrorLine($line))->all();
	}
}