<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use Laragear\WebAuthn\Models\WebAuthnCredential;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class WebAuthnResource extends Data
{
	public string $id;
	public ?string $alias;
	public string $created_at;

	public function __construct(WebAuthnCredential $credential)
	{
		$this->id = $credential->id;
		$this->alias = $credential->alias;
		$this->created_at = $credential->created_at->toIso8601String();
	}

	public static function fromModel(WebAuthnCredential $credential): WebAuthnResource
	{
		return new self($credential);
	}
}
